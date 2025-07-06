let userManuallyScrolled = false;
let selectedUserId = null;
let notifiedMessages = new Set(); // track by message_id

const notificationToggle = document.getElementById("notificationsToggle");

// ✅ Set toggle checkbox state from localStorage
if (localStorage.getItem("notificationsEnabled") === "true") {
  notificationToggle.checked = true;
}

// ✅ Handle toggle changes
notificationToggle?.addEventListener("change", async () => {
  if (notificationToggle.checked) {
    const permission = await Notification.requestPermission();
    if (permission === "granted") {
      localStorage.setItem("notificationsEnabled", "true");
      alert("🔔 Notifications enabled!");
    } else {
      notificationToggle.checked = false;
      alert("❌ Notifications blocked by the browser.");
    }
  } else {
    localStorage.setItem("notificationsEnabled", "false");
  }
});

  function downloadFile(filePath) {
    const link = document.createElement("a");
    link.href = filePath;
    link.download = ""; // Auto uses filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  async function downloadFileAs(fileUrl, suggestedFileName = "download") {
    try {
      const response = await fetch(fileUrl);
      const blob = await response.blob();

      // Check for File System Access API support
      if (window.showSaveFilePicker) {
        const fileHandle = await window.showSaveFilePicker({
          suggestedName: suggestedFileName,
          types: [{
            description: 'File',
            accept: { [blob.type]: ['.' + suggestedFileName.split('.').pop()] }
          }]
        });

        const writableStream = await fileHandle.createWritable();
        await writableStream.write(blob);
        await writableStream.close();
      } else {
        // Fallback to default download
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = suggestedFileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    } catch (err) {
      console.error("Save As failed:", err);
      alert("❌ Failed to save file.");
    }
  }

  function openLightbox(url) {
    const overlay = document.createElement("div");
    overlay.className = "lightbox";
    overlay.innerHTML = `
      <div class="lightbox-content">
        <img src="${url}" />
        <button class="close-lightbox">×</button>
      </div>`;
    document.body.appendChild(overlay);

    overlay.querySelector(".close-lightbox").onclick = () => overlay.remove();
  }


document.addEventListener("DOMContentLoaded", () => {
  let selectedMessageIds = new Set(); 
  let typingTimeout;
  let communicatedUsers = [];
  let lastMessageIdMap = {};
  let initialLoadDone = {};
  let isEditing = false;
  let editingMessageId = null;
  let currentSearchTerm = "";


  const userList = document.getElementById("userList");
  const chatBox = document.getElementById("chatBox");

  let distanceFromBottom = 0;
  const threshold = 50;
  let shouldScrollToBottom = false;

  chatBox.addEventListener("scroll", () => {
    distanceFromBottom = chatBox.scrollHeight - (chatBox.scrollTop + chatBox.clientHeight);
    userManuallyScrolled = distanceFromBottom > threshold;
  });


  const chatForm = document.getElementById("chatForm");
  const messageInput = document.getElementById("messageInput");
  const searchUser = document.getElementById("searchUser");
  const profileModal = document.getElementById("profileModal");
  const currentUsername = document.body.dataset.username;
  const sidebar = document.getElementById("userSidebar");
  const chatPanel = document.querySelector(".chat-panel");
  const chatButton = document.getElementById("toggleSidebar");
  const fileInput = document.getElementById("fileInput");
  const attachmentPreview = document.getElementById("attachmentPreview");
  const emojiButton = document.getElementById("emojiButton");
  const emojiPicker = document.getElementById("emojiPicker");

// Toggle emoji picker display
emojiButton?.addEventListener("click", (e) => {
  e.stopPropagation(); // prevent bubbling to document
  const isVisible = emojiPicker.style.display === "block";
  emojiPicker.style.display = isVisible ? "none" : "block";
});

// Insert emoji on click
emojiPicker?.addEventListener("click", (e) => {
  if (e.target.classList.contains("emoji")) {
    messageInput.value += e.target.textContent;
    messageInput.focus();
  }
});

// Close picker if clicked outside
document.addEventListener("click", (e) => {
  if (!emojiPicker.contains(e.target) && e.target !== emojiButton) {
    emojiPicker.style.display = "none";
  }
});

fileInput.addEventListener("change", () => {
  const file = fileInput.files[0];
  attachmentPreview.innerHTML = "";

  if (!file) {
    attachmentPreview.style.display = "none";
    return;
  }

    const fileType = file.type;
    const container = document.createElement("div");
    container.className = "attachment-preview-box";

    if (fileType.startsWith("image/")) {
      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      img.onload = () => URL.revokeObjectURL(img.src);
      img.className = "preview-image";
      container.appendChild(img);
      img.width = 300; // Set width in pixels
      img.height = 200; // Set height in pixels

    } else {
      const fileText = document.createElement("div");
      fileText.textContent = `📎 ${file.name}`;
      fileText.className = "preview-filename";
      container.appendChild(fileText);
    }

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "×";
    removeBtn.className = "remove-preview-btn";
    removeBtn.onclick = () => {
      fileInput.value = "";
      attachmentPreview.style.display = "none";
      attachmentPreview.innerHTML = "";
    };
    container.appendChild(removeBtn);

    attachmentPreview.appendChild(container);
    attachmentPreview.style.display = "flex";
  });

  function setActiveSidebar(section) {
    document.querySelectorAll(".nav-icon").forEach(icon => {
      icon.classList.toggle("active", icon.dataset.section === section);
    });
  }

  const toggleVisibilitySection = document.getElementById("toggleVisibilitySection");
  const visibilityContainer = document.getElementById("visibilityContainer");
  const visibilityArrow = document.getElementById("visibilityArrow");

  toggleVisibilitySection?.addEventListener("click", async () => {
    const isVisible = visibilityContainer.style.display === "block";
    visibilityContainer.style.display = isVisible ? "none" : "block";
    visibilityArrow.style.transform = isVisible ? "rotate(0deg)" : "rotate(180deg)";

    // Load visibility list only if opening
    if (!isVisible) {
      const res = await fetch("includes/getUsersWithVisibility.php");
      const data = await res.json();

      if (data.status === "success") {
        visibilityUserList.innerHTML = "";
        data.users.forEach(user => {
          const label = document.createElement("label");
          label.style.display = "block";
          label.innerHTML = `
            <input type="checkbox" value="${user.user_id}" ${user.is_allowed ? "checked" : ""} />
            ${user.username}
          `;
          visibilityUserList.appendChild(label);
        });
      } else {
        visibilityUserList.innerHTML = "<p style='color:red;'>❌ Failed to load users.</p>";
      }
    }
  });


  const saveVisibilityBtn = document.getElementById("saveVisibilityBtn");
  const visibilityUserList = document.getElementById("visibilityUserList");

  saveVisibilityBtn?.addEventListener("click", async () => {
    const checkboxes = visibilityUserList.querySelectorAll("input[type='checkbox']");
    const allowed_ids = Array.from(checkboxes)
      .filter(cb => cb.checked)
      .map(cb => parseInt(cb.value));

    const res = await fetch("includes/saveProfileVisibility.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ allowed_ids })
    });

    const data = await res.json();

    if (data.status === "success") {
      alert("✅ Profile visibility saved.");
    } else {
      alert("❌ Failed to save visibility.");
    }
  });


  const typingIndicator = document.createElement("div");
  typingIndicator.id = "typingIndicator";
  typingIndicator.textContent = "Typing...";
  typingIndicator.style.cssText = "font-style: italic; margin: 0.5em 0; display: none;";
  chatBox.appendChild(typingIndicator);

  function renderUserList(users) {
    userList.innerHTML = "";
    users.forEach(user => {
      const li = document.createElement("li");
      li.dataset.id = user.user_id;
      li.classList.add("user-item");

      const profilePic = user.profile_pic || 'assets/images/default-avatar.webp';
      

      const isMuted = isUserMuted(user.user_id);
      const muteBadge = isMuted ? `<span class="mute-badge" title="Muted">🔇</span>` : "";

      const unreadBadge = user.unread_count > 0
        ? `<span class="unread-count">${user.unread_count}</span>`
        : "";


    li.innerHTML = `
      <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <img src="${profilePic}" class="user-avatar" alt="${user.username}" />
          <div style="display: flex; align-items: center; gap: 5px;">
            <span class="user-name">${user.username}</span>
            ${muteBadge}
          </div>
        </div>
        ${unreadBadge}
      </div>
    `;



      li.addEventListener("click", () => selectUser(user));
      userList.appendChild(li);
    });
  }


  async function fetchCommunicatedUsers() {
    try {
      const res = await fetch("includes/getRecentUsers.php");
      const text = await res.text();

      try {
        const data = JSON.parse(text);
        if (data.status === "success") {
          communicatedUsers = data.users || [];
          if (!currentSearchTerm) renderUserList(communicatedUsers);
        } else {
          console.warn("Unexpected response:", data);
        }
      } catch (jsonErr) {
        console.error("Invalid JSON in response:", text);
      }
    } catch (err) {
      console.error("Failed to fetch recent users:", err);
    }
  }

  function isUserMuted(userId) {
    const muted = JSON.parse(localStorage.getItem("mutedUsers") || "[]");
    return muted.includes(userId);
  }

  function toggleMuteUser(userId) {
    let muted = JSON.parse(localStorage.getItem("mutedUsers") || "[]");
    if (muted.includes(userId)) {
      muted = muted.filter(id => id !== userId);
    } else {
      muted.push(userId);
    }
    localStorage.setItem("mutedUsers", JSON.stringify(muted));
  }

    async function searchUsers(term) {
    const formData = new FormData();
    formData.append("term", term);

    try {
      const res = await fetch("includes/searchUsers.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();
      // only update if term still matches
      if (data.users && term === currentSearchTerm) {
        renderUserList(data.users);
      }
    } catch (err) {
      console.error("Search error:", err);
    }
  }
  
  async function selectUser(user) {
    selectedUserId = user.user_id;
    // ✅ Show chat input area and hide placeholder when user is selected
    document.getElementById("chatInputArea").style.display = "block";
    document.getElementById("chatPlaceholder").style.display = "none";

    const chatTitle = document.getElementById("chatTitle");
    const profilePic = user.profile_pic || "assets/images/default-avatar.webp";

    chatTitle.innerHTML = `
      <div class="chat-header-container" style="display: flex; align-items: center; gap: 10px;">
        <img src="${profilePic}" class="chat-user-pic" />
        <div class="chat-user-status">
          <span class="chat-username" style="font-weight: bold;">${user.username}</span><br>
          <span class="status-text ${user.status === "online" ? "online" : "offline"}">
            ${user.status === "online" ? "Online" : "Offline"}
          </span>
        </div>
        <div class="chat-menu-wrapper" style="margin-left:auto;">
          <span id="chatMenuIcon" title="Options">&#8942;</span>
          <div id="chatMenu" class="chat-dropdown" style="display: none;">
            <div class="chat-option" id="clearChatOption">🧹 Clear Chat</div>
            <div class="chat-option" id="muteToggleOption">🔇 Mute chat</div>
            <div class="chat-option" id="blockUserOption">🚫 Block User</div>
          </div>
        </div>
      </div>
    `;  

    setTimeout(() => {
      const chatImg = document.querySelector(".chat-user-pic"); // class from chatTitle image
      const modal = document.getElementById("profilePicModal");
      const modalImg = document.getElementById("profilePicModalImg");

      if (chatImg && modal && modalImg) {
        chatImg.addEventListener("click", () => {
          modalImg.src = chatImg.src;
          modal.style.display = "flex";
        });
      }

      document.querySelector("#profilePicModal .modal-blur-bg")?.addEventListener("click", () => {
        modal.style.display = "none";
      });
    }, 100);

    if (window.innerWidth <= 800) switchToChatPanelMobile();
    window.scrollTo(0, 0);

    if (!communicatedUsers.find(u => u.user_id === user.user_id)) {
      communicatedUsers.push(user);
    }

    // ✅ Reset unread count when user is selected
    const selected = communicatedUsers.find(u => u.user_id === user.user_id);
    if (selected) {
      selected.unread_count = 0;
    }


    renderUserList(communicatedUsers);
    searchUser.value = "";
    currentSearchTerm = "";
    initialLoadDone[selectedUserId] = false;

    await loadMessages(true);
    userManuallyScrolled = false;
  
    const muteToggleOption = document.getElementById("muteToggleOption");

    if (isUserMuted(selectedUserId)) {
      muteToggleOption.textContent = "🔊 Unmute chat";
    } else {
      muteToggleOption.textContent = "🔇 Mute chat";
    }

    muteToggleOption?.addEventListener("click", () => {
      toggleMuteUser(selectedUserId);

      // Update menu label
      muteToggleOption.textContent = isUserMuted(selectedUserId)
        ? "🔊 Unmute chat"
        : "🔇 Mute chat";

      alert(`${isUserMuted(selectedUserId) ? "🔕 User muted." : "🔔 User unmuted."}`);
      renderUserList(communicatedUsers); // Refresh sidebar mute badge
    });


    const chatMenuIcon = document.getElementById("chatMenuIcon");
    const chatMenu = document.getElementById("chatMenu");

    chatMenuIcon?.addEventListener("click", (e) => {
      chatMenu.style.display = chatMenu.style.display === "block" ? "none" : "block";
      e.stopPropagation();
    });

    document.addEventListener("click", (e) => {
      if (!chatMenu.contains(e.target) && e.target !== chatMenuIcon) {
        chatMenu.style.display = "none";
      }
    });

    document.getElementById("clearChatOption")?.addEventListener("click", async () => {
      if (!selectedUserId) return;
      const res = await fetch("includes/clearChat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ receiver_id: selectedUserId })
      });
      const data = await res.json();
      if (data.status === "success") {
        loadMessages(true);
        alert("Chat cleared.");
      }
    });

    document.getElementById("blockUserOption")?.addEventListener("click", async () => {
      if (!selectedUserId) return;
      const res = await fetch("includes/blockUser.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ blocked_id: selectedUserId })
      });
      const data = await res.json();
      if (data.status === "success") {
        selectedUserId = null;
        chatBox.innerHTML = "";
   

    if (!initialLoadDone[selectedUserId]) {
      chatBox.scrollTop = chatBox.scrollHeight; // First time always scroll
    } else if (shouldScrollToBottom && chatBox.scrollHeight > 0) {
      chatBox.scrollTop = chatBox.scrollHeight; // Scroll only if near bottom
    }


        chatTitle.textContent = "";
        document.getElementById("chatInputArea").style.display = "none";
      document.getElementById("chatPlaceholder").style.display = "block";

        alert("User blocked.");
        fetchCommunicatedUsers();
      }
    });

  }

  window.selectUser = selectUser;

  async function loadMessages(scrollToBottom = false) {

    if (!selectedUserId || !chatBox) return;

    shouldScrollToBottom = scrollToBottom || distanceFromBottom <= threshold;

    try {
      const res = await fetch(`includes/getMessage.php?receiver_id=${selectedUserId}`);
      const data = await res.json();
      if (!data.messages) return;

      chatBox.innerHTML = "";
      let lastDate = null;
      const messages = data.messages;
      const latestMsg = messages[messages.length - 1];

      messages.forEach(msg => {
        if (msg.date !== lastDate) {
          const dateDiv = document.createElement("div");
          dateDiv.className = "date-divider";
          dateDiv.textContent = msg.date;
          chatBox.appendChild(dateDiv);
          lastDate = msg.date;
        }

        const div = document.createElement("div");
        div.classList.add("message", msg.sender === currentUsername ? "sent" : "received");
        const inner = document.createElement("div");

        let filePreview = "";
        if (msg.file_path) {
          const fileName = msg.file_path.split('/').pop();
          const ext = fileName.split('.').pop().toLowerCase();

          filePreview = `
            <div class="chat-file">
              ${['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)
                ? `<img src="${msg.file_path}" class="chat-image"/>`
                : ['mp4', 'webm', 'ogg'].includes(ext)
                  ? `<video src="${msg.file_path}" controls class="chat-video"></video>`
                  : `<div class="file-preview">
                      <i class="fa-solid fa-file-lines"></i> ${fileName}
                    </div>`}

              ${msg.sender !== currentUsername ? `
                <div class="file-buttons">
                  <a href="${msg.file_path}" target="_blank" class="download-btn">🔓 Open</a>
                  <button onclick="downloadFileAs('${msg.file_path}', '${fileName}')" class="download-btn">💾 Save As</button>
                </div>` : ""}
            </div>`;
        }


        if (msg.file_path) {
          const fileName = msg.file_path.split('/').pop();
          const isImage = ['jpg', 'jpeg', 'png', 'gif','webp'].includes(msg.file_type);

          fileLink = isImage
            ? `<div><img src="${msg.file_path}" alt="Attachment" style="max-width: 200px; margin-top: 5px;" /></div>`
            : `<div><a href="${msg.file_path}" target="_blank"><i class="fas fa-paperclip"></i> ${fileName}</a></div>`;
        }

        inner.innerHTML = `
          <strong>${msg.sender === currentUsername ? "You" : msg.sender}:</strong>
          <span class="message-text" data-id="${msg.id}">${msg.message}</span>
          ${filePreview}
          <div class="msg-time">${msg.time}</div>
        `;


        div.appendChild(inner);

        if (msg.sender === currentUsername) {
          const iconGroup = document.createElement("div");
          iconGroup.className = "icon-group";

          // Build HTML conditionally
          let iconHtml = `
            <input type="checkbox" class="select-msg" data-id="${msg.id}" ${selectedMessageIds.has(msg.id.toString()) ? "checked" : ""} />
          `;

          // ❌ Don't show edit button if file is attached
          if (!msg.file_path) {
            iconHtml += `
              <button class="edit-icon" data-id="${msg.id}" data-message="${msg.message}" title="Edit">
                <i class="fa-solid fa-pencil"></i>
              </button>
            `;
          }

          iconHtml += `
            <button class="delete-icon" data-id="${msg.id}" title="Delete">
              <i class="fa-solid fa-trash"></i>
            </button>
          `;

          iconGroup.innerHTML = iconHtml;
          div.prepend(iconGroup);
        }


        if (msg.sender === currentUsername && msg.seen_status === 1) {
          const seen = document.createElement("div");
          seen.className = "seen-status";
          seen.textContent = "Seen";
          seen.style.cssText = "font-size: 11px; text-align: right; margin-right: 0;";
          div.appendChild(seen);
        }

        chatBox.appendChild(div);

        // Add checkbox change event
        div.querySelectorAll(".select-msg").forEach(checkbox => {
          checkbox.addEventListener("change", () => {
            const id = checkbox.dataset.id;
            if (checkbox.checked) {
              selectedMessageIds.add(id);
            } else {
              selectedMessageIds.delete(id);
            }

            // Toggle the visibility of the bulk delete container
            const container = document.getElementById("deleteSelectedContainer");
            if (container) {
              container.style.display = selectedMessageIds.size > 0 ? "block" : "none";
            }
          });
        });

      });

      chatBox.appendChild(typingIndicator);
      typingIndicator.style.display = "none";

      if (!initialLoadDone[selectedUserId]) {
        chatBox.scrollTop = chatBox.scrollHeight; // First time auto-scroll
      } else if (shouldScrollToBottom && chatBox.scrollHeight > 0) {
        chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll only if near bottom
      }


      document.querySelectorAll(".edit-icon").forEach(icon => {
        icon.addEventListener("click", () => {
          messageInput.value = icon.dataset.message;
          editingMessageId = icon.dataset.id;
          isEditing = true;
          messageInput.focus();
        });
      });

      document.querySelectorAll(".delete-icon").forEach(icon => {
        icon.addEventListener("click", async () => {
          if (confirm("Delete this message?")) {
            const res = await fetch("includes/deleteMessage.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ message_id: icon.dataset.id })
            });
            const data = await res.json();
            if (data.status === "success") loadMessages(true);
          }
        });
      });


    if (!initialLoadDone[selectedUserId]) {
      chatBox.scrollTop = chatBox.scrollHeight; // First time, scroll to bottom
    } else if (shouldScrollToBottom && chatBox.scrollHeight > 0) {
      chatBox.scrollTop = chatBox.scrollHeight; // Scroll only if user is near bottom
    }



      await fetch("includes/markAsSeen.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sender_id: selectedUserId })
      });


    // ✅ Show notification if enabled and a new message arrives
    if (
      latestMsg &&
      latestMsg.sender !== currentUsername &&
      (!selectedUserId || latestMsg.sender_id !== selectedUserId) &&
      latestMsg.id > lastMessageIdMap[latestMsg.sender_id ?? selectedUserId] &&
      !isUserMuted(latestMsg.sender_id ?? selectedUserId) // ✅ skip if muted
    ) {
      try {
        if (
          localStorage.getItem("notificationsEnabled") === "true" &&
          Notification.permission === "granted"
        ) {
          new Notification(`📩 Message from ${latestMsg.sender}`, {
            body: latestMsg.message,
            icon: "assets/images/icon.png"
          });
        }
      } catch (e) {
        console.warn("❌ Notification failed:", e);
      }
    }

      if (latestMsg?.id) {
        lastMessageIdMap[selectedUserId] = latestMsg.id;
        lastMessageIdMap[latestMsg.sender_id] = latestMsg.id;
      }

      if (!initialLoadDone[selectedUserId]) initialLoadDone[selectedUserId] = true;

    } catch (err) {
      console.error("Load messages failed", err);
    }

    const deleteContainer = document.getElementById("deleteSelectedContainer");
    if (deleteContainer) {
      deleteContainer.style.display = selectedMessageIds.size > 0 ? "block" : "none";
}

  }

  document.getElementById("deleteSelectedBtn")?.addEventListener("click", async () => {
    if (selectedMessageIds.size === 0) return;
    if (!confirm("Are you sure you want to delete selected messages?")) return;

    const res = await fetch("includes/deleteMultipleMessages.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message_ids: Array.from(selectedMessageIds) })
    });
    const data = await res.json();
    if (data.status === "success") {
      selectedMessageIds.clear();
      document.getElementById("deleteSelectedContainer").style.display = "none";
      loadMessages(true);
    }
  });

  chatForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!selectedUserId) {
      showToast("❗ Please select a user to send a message.");
      return;
    }


  const message = messageInput.value.trim();
  const fileInput = document.getElementById("fileInput");
  const file = fileInput?.files?.[0];

  // ✅ Don't submit if nothing provided
  if (!message && !file) return;

  // ✅ If editing, call edit endpoint instead
  if (isEditing && editingMessageId) {
    const res = await fetch("includes/editMessage.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: editingMessageId, message })
    });
    const data = await res.json();
    if (data.status === "success") {
      isEditing = false;
      editingMessageId = null;
      messageInput.value = "";
      loadMessages(true);
    } else {
         messageInput.value = "";
    }
    return;
  }

  // ✅ New message logic with file
  const formData = new FormData();
  formData.append("message", message);
  formData.append("receiver_id", selectedUserId);
  if (file) formData.append("attachment", file);

  const res = await fetch("includes/sendMessage.php", {
    method: "POST",
    body: formData // DO NOT set headers manually
  });

  const data = await res.json();

  if (data.status === "success") {
    messageInput.value = "";
    if (fileInput) fileInput.value = "";
    document.getElementById("attachmentPreview").style.display = "none";
    document.getElementById("attachmentPreview").innerHTML = "";
    loadMessages(true);
    userManuallyScrolled = false;
  } else {
    alert(data.message || "Failed to send message.");
  }
});




  messageInput?.addEventListener("input", () => {
    if (!selectedUserId) return;
    fetch("includes/setTypingStatus.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ receiver_id: selectedUserId, is_typing: 1 })
    });

    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
      fetch("includes/setTypingStatus.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ receiver_id: selectedUserId, is_typing: 0 })
      });
    }, 5000);
  });

  async function checkTypingStatus() {
    if (!selectedUserId) return;
    try {
      const res = await fetch(`includes/getTypingStatus.php?sender_id=${selectedUserId}`);
      const data = await res.json();
      typingIndicator.style.display = data.is_typing ? "block" : "none";
    } catch (err) {
      console.warn("Typing check failed", err);
    }
  }

  searchUser?.addEventListener("input", () => {
    const term = searchUser.value.trim().toLowerCase();
    currentSearchTerm = term;
    if (!term) {
      renderUserList(communicatedUsers);
    } else {
      searchUsers(term);
    }
  });

  chatButton?.addEventListener("click", () => {
    sidebar.classList.add("show");
    chatPanel.classList.remove("visible-on-mobile");
    chatPanel.classList.add("hidden-on-mobile");
  });

  function switchToChatPanelMobile() {
    if (window.innerWidth <= 768) {
      sidebar.classList.remove("show");
      chatPanel.classList.remove("hidden-on-mobile");
      chatPanel.classList.add("visible-on-mobile");
    }
  }

  // Profile Modal
document.getElementById("helloUsername")?.addEventListener("click", async () => {
  if (!profileModal) return;
  profileModal.style.display = "flex";  // ✅ Use 'flex' so it's centered

});
document.getElementById("closeProfile")?.addEventListener("click", () => {
  profileModal.style.display = "none";  // ✅ Properly hide the modal
});

  function checkForUnseenMessages() {
    fetch("includes/checkUnseenMessages.php")
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          data.unseen.forEach(({ sender_id, username, count }) => {
          
            data.unseen.forEach(({ sender_id, username, count, latest_message_id }) => {
            const muted = JSON.parse(localStorage.getItem("mutedUsers") || "[]");

            if (
              parseInt(sender_id) !== selectedUserId &&
              !notifiedMessages.has(latest_message_id) &&
              !muted.includes(parseInt(sender_id))
            ) {
              showToast(`📩 ${count} new message(s) from ${username}`);
              showBrowserNotification(username, count, sender_id);
              notifiedMessages.add(latest_message_id);
            }
            });

          });
        }
      })
      .catch(err => {
        console.error("Failed to check unseen messages:", err);
      });
  }


  setInterval(checkForUnseenMessages, 10000); 

  function showToast(message) {
    const container = document.getElementById("toastContainer");
    const toast = document.createElement("div");
    toast.className = "toast-message";
    toast.innerText = message;
    toast.style.cssText = `
      background: #444;
      color: #fff;
      padding: 10px 15px;
      margin-top: 10px;
      border-radius: 6px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
  }



  function showBrowserNotification(username, count, senderId) {
    try {
      // ✅ Prevent notification if this user is muted
      const muted = JSON.parse(localStorage.getItem("mutedUsers") || "[]");
      if (muted.includes(parseInt(senderId))) return;

      if (
        localStorage.getItem("notificationsEnabled") === "true" &&
        Notification.permission === "granted"
      ) {
        new Notification(`📩 Message from ${username}`, {
          body: `${count} new message(s) waiting.`,
          icon: "assets/images/icon.png"
        });
      }
    } catch (e) {
      console.warn("❌ Notification error:", e);
    }
  }


  fetchCommunicatedUsers();
  setInterval(() => {
    if (selectedUserId) {
      loadMessages();
      checkTypingStatus();
    }
    
    fetchCommunicatedUsers();
  }, 5000);

  setActiveSidebar("chat");
});
