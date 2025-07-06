  <?php
  require_once 'includes/sessions.php';
  require_login();
  require_once 'includes/db.php';

  $current_id = $_SESSION['user_id'];
  $current_username = $_SESSION['username'];
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lets Talk Together</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  </head>
  <body data-username="<?php echo htmlspecialchars($current_username); ?>" data-user-id="<?php echo $_SESSION['user_id']; ?>">

  <?php include 'includes/navbar.php'; ?>

  <div class="chat">
    <!-- Sidebar Navbar -->
    <div class="left-navbar">
      <div class="nav-icon" id="toggleSidebar" title="Chats">
        <i class="fas fa-comment-dots"></i>
      </div>
      <div class="nav-icon" title="Settings" id="settingsIcon">
        <i class="fas fa-cog"></i>
      </div>
      <div class="nav-icon" title="Profile" id="helloUsername">
        <i class="fas fa-user-circle"></i>
      </div>
      <div class="nav-icon" title="Logout">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </div>

    <!-- User list -->
    <div class="sidebar" id="userSidebar">
      <input type="text" id="searchUser" placeholder="Search user...">
      <ul id="userList"></ul>
    </div>

    <!-- Chat panel -->
    <div class="chat-panel" hidden-on-mobile id="chatPanel">
      <div class="chat-header" id="chatHeader">
        <span id="chatTitle" class="chat-title"><i class="fas fa-user"></i> User</span>
      </div>

      <div id="chatPlaceholder" style="display: flex; justify-content: center; align-items: center; height: calc(100% - 60px); text-align: center; font-size: 1.2em;">
        <p>👈 Select a user from the left panel to start chatting.</p>
      </div>

      <div id="deleteSelectedContainer" style="display:none; text-align:right; padding:5px 10px;">
        <button id="deleteSelectedBtn" class="btn btn-danger btn-sm">🗑️ Delete Selected</button>
      </div>

      <div class="chat-box" id="chatBox"></div>

      <div id="chatInputArea" style="display: none;">
        <div id="attachmentPreview"></div>

        <form id="chatForm" enctype="multipart/form-data">
          <button type="button" id="emojiButton" class="emoji-button" title="Insert Emoji">
            <i class="fa-solid fa-face-smile"></i>
          </button>

          <label for="fileInput" class="file-label"><i class="fas fa-paperclip"></i></label>
          <input type="file" id="fileInput" style="display: none;" />
          <input type="text" id="messageInput" placeholder="Type a message..." />
          <button type="submit" class="send-button">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
        <div id="emojiPicker" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 10;">
          <span class="emoji">😀</span>
          <span class="emoji">😂</span>
          <span class="emoji">😍</span>
          <span class="emoji">😊</span>
          <span class="emoji">😢</span>
          <span class="emoji">😭</span>
          <span class="emoji">😡</span>
          <span class="emoji">😎</span>
          <span class="emoji">😅</span>
          <span class="emoji">😉</span>
          <span class="emoji">🤔</span>
          <span class="emoji">😴</span>
          <span class="emoji">🥰</span>
          <span class="emoji">😇</span>
          <span class="emoji">🤗</span>
          <span class="emoji">🙃</span>
          <span class="emoji">🫠</span>
          <span class="emoji">🥲</span>
          <span class="emoji">🤩</span>
          <span class="emoji">😬</span>

          <!-- Gestures -->
          <span class="emoji">👍</span>
          <span class="emoji">👎</span>
          <span class="emoji">👏</span>
          <span class="emoji">🙌</span>
          <span class="emoji">🙏</span>
          <span class="emoji">🤝</span>
          <span class="emoji">👌</span>
          <span class="emoji">🤞</span>
          <span class="emoji">✌️</span>
          <span class="emoji">👋</span>
          <span class="emoji">✊</span>
          <span class="emoji">🖐️</span>
          <span class="emoji">🤙</span>

          <!-- Love & Celebration -->
          <span class="emoji">❤️</span>
          <span class="emoji">💔</span>
          <span class="emoji">💕</span>
          <span class="emoji">💖</span>
          <span class="emoji">💘</span>
          <span class="emoji">💝</span>
          <span class="emoji">🎉</span>
          <span class="emoji">🎊</span>
          <span class="emoji">🎁</span>
          <span class="emoji">💐</span>
          <span class="emoji">🌹</span>

          <!-- Food -->
          <span class="emoji">🍕</span>
          <span class="emoji">🍔</span>
          <span class="emoji">🍟</span>
          <span class="emoji">🌭</span>
          <span class="emoji">🍿</span>
          <span class="emoji">🍩</span>
          <span class="emoji">🍪</span>
          <span class="emoji">🍫</span>
          <span class="emoji">🍰</span>
          <span class="emoji">🍦</span>

          <!-- Animals -->
          <span class="emoji">🐶</span>
          <span class="emoji">🐱</span>
          <span class="emoji">🐭</span>
          <span class="emoji">🐹</span>
          <span class="emoji">🐰</span>
          <span class="emoji">🦊</span>
          <span class="emoji">🐻</span>
          <span class="emoji">🐼</span>
          <span class="emoji">🦁</span>
          <span class="emoji">🐨</span>

          <!-- Travel & Places -->
          <span class="emoji">🚗</span>
          <span class="emoji">✈️</span>
          <span class="emoji">🚀</span>
          <span class="emoji">🚂</span>
          <span class="emoji">🗽</span>
          <span class="emoji">🏖️</span>
          <span class="emoji">🏰</span>
          <span class="emoji">🏔️</span>
          <span class="emoji">🌃</span>
          <span class="emoji">🗺️</span>
        </div>
       </div>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <div id="profileModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" id="closeProfile">&times;</span>
      <h4>👤 Your Profile</h4>

      <p><strong>Username:</strong>
        <span id="usernameDisplay"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <button id="editUsernameBtn" class="btn btn-sm btn-outline-secondary">✏️ Edit</button>
      </p>

      <div id="editUsernameContainer" style="display: none; margin-top: 10px;">
        <input type="text" id="newUsernameInput" class="form-control mb-1" placeholder="Enter new username" />
        <button id="saveUsernameBtn" class="btn btn-success btn-sm">Save</button>
        <button id="cancelUsernameBtn" class="btn btn-secondary btn-sm">Cancel</button>
        <p id="usernameStatus" style="margin-top: 5px; font-size: 0.9em;"></p>
      </div>

      <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
      <hr>

      <!-- Profile Picture Section -->
      <div style="text-align: center; margin-bottom: 10px;">
        <img id="profilePreview"
          src="<?php echo isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) 
                        ? htmlspecialchars($_SESSION['profile_pic']) 
                        : 'assets/images/default-avatar.webp'; ?>"
          alt="Profile Picture"
          style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; cursor: pointer;" />
      </div>

      <!-- Upload Form -->
      <form id="uploadProfileForm" enctype="multipart/form-data" style="text-align: center;">
        <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" class="form-control" />
        <button type="submit" class="btn btn-primary btn-sm mt-2">Upload Picture</button>
      </form>
    </div>
  </div>

  <!-- Modal Preview for Profile Picture -->
  <div id="profilePicModal" class="modal" style="display:none;">
    <div class="modal-blur-bg"></div>
    <div class="modal-center-img">
      <img id="profilePicModalImg" src="" alt="Profile Preview" />
    </div>
  </div>



  <!-- Settings Modal -->
  <div id="settingsModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" id="closeSettings">&times;</span>
      <h4 style="margin-bottom: 20px; text-align:center;">⚙️ Settings</h4>

      <div class="setting-item">
        <label>
          <input type="checkbox" id="darkModeToggle"> 🌗 Enable Dark Mode
        </label>

        <!-- Inside your #settingsModal .modal-content -->
        <hr>
        <div class="blocked-section">
          <div class="blocked-header" id="toggleBlockedList">
            <span>🚫 Blocked Users</span>
            <i class="fas fa-chevron-down" id="blockedArrow" style="transition: 0.3s;"></i>
          </div>
          <ul id="blockedUsersContainer" style="display: none;"></ul>
          <hr>
          <label><input type="checkbox" id="notificationsToggle"> 🔔 Enable Notifications</label>
          
          <hr>
          <button id="deleteAccountBtn" class="btn btn-danger">🗑️ Delete Account</button>
          <hr>
          <div id="toggleVisibilitySection" style="cursor: pointer; font-weight: bold;">
            👁️ Profile Picture Visibility <i class="fas fa-chevron-down" id="visibilityArrow" style="transition: 0.3s;"></i>
          </div>

          <div id="visibilityContainer" style="display: none; margin-top: 10px;">
            <p style="font-size: 0.9em;">Select users who can see your profile picture:</p>
            <div id="visibilityUserList" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
              <p>Loading...</p>
            </div>
            <button id="saveVisibilityBtn" class="btn btn-sm btn-success mt-2">💾 Save Visibility</button>
          </div>


        <hr>
        <div id="togglePasswordSection" style="cursor: pointer; font-weight: bold;">
          🔐 Change Password <i class="fas fa-chevron-down" id="passwordArrow" style="transition: 0.3s;"></i>
        </div>

        <div id="passwordFormContainer" style="display: none; margin-top: 10px;">
          <input type="password" id="currentPassword" class="form-control mb-2" placeholder="Current Password">
          <input type="password" id="newPassword" class="form-control mb-2" placeholder="New Password">
          <input type="password" id="confirmPassword" class="form-control mb-2" placeholder="Confirm New Password">
          <button id="updatePasswordBtn" class="btn btn-success btn-sm">Update Password</button>
          <p id="passwordStatus" style="font-size: 0.9em; margin-top: 5px;"></p>
        </div>

    </div>
  </div>

  <!-- JavaScript -->
  <script src="assets/js/script.js"></script>

<script>
  
  document.addEventListener("DOMContentLoaded", () => {
    const settingsIcon = document.getElementById("settingsIcon");
    const settingsModal = document.getElementById("settingsModal");
    const closeSettings = document.getElementById("closeSettings");
    const darkModeToggle = document.getElementById("darkModeToggle");
    const blockedUsersContainer = document.getElementById("blockedUsersContainer");
    const blockedArrow = document.getElementById("blockedArrow");
    const toggleBlockedList = document.getElementById("toggleBlockedList");
    const togglePasswordSection = document.getElementById("togglePasswordSection");
    const passwordFormContainer = document.getElementById("passwordFormContainer");
    const passwordArrow = document.getElementById("passwordArrow");
    const editBtn = document.getElementById("editUsernameBtn");
    const saveBtn = document.getElementById("saveUsernameBtn");
    const cancelBtn = document.getElementById("cancelUsernameBtn");
    const usernameDisplay = document.getElementById("usernameDisplay");
    const editUsernameContainer = document.getElementById("editUsernameContainer");
    const newUsernameInput = document.getElementById("newUsernameInput");

  
    editBtn?.addEventListener("click", () => {
      newUsernameInput.value = usernameDisplay.textContent.trim();
      editUsernameContainer.style.display = "block";
      editBtn.style.display = "none";
    });

    cancelBtn?.addEventListener("click", () => {
      editUsernameContainer.style.display = "none";
      editBtn.style.display = "inline-block";
      usernameStatus.textContent = "";
    });

    newUsernameInput?.addEventListener("input", async () => {
      const newUsername = newUsernameInput.value.trim();
      if (newUsername === "") {
        usernameStatus.textContent = "";
        return;
      }

      const formData = new FormData();
      formData.append("username", newUsername);

      const res = await fetch("includes/checkUsername.php", {
        method: "POST",
        body: formData
      });

      const data = await res.json();
      if (data.exists) {
        usernameStatus.textContent = "❌ Username is already taken.";
        usernameStatus.style.color = "red";
      } else {
        usernameStatus.textContent = "✅ Username is available.";
        usernameStatus.style.color = "green";
      }
    });

    saveBtn?.addEventListener("click", async () => {
      const newUsername = newUsernameInput.value.trim();
      if (!newUsername) {
        usernameStatus.textContent = "❗ Please enter a username.";
        usernameStatus.style.color = "red";
        return;
      }

      const formData = new FormData();
      formData.append("username", newUsername);

      const checkRes = await fetch("includes/checkUsername.php", {
        method: "POST",
        body: formData
      });

      const checkData = await checkRes.json();
      if (checkData.exists) {
        usernameStatus.textContent = "❌ Username already exists.";
        usernameStatus.style.color = "red";
        return;
      }

      // ✅ Username is unique, update
      const updateRes = await fetch("includes/updateUsername.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username: newUsername })
      });

      const updateData = await updateRes.json();
      if (updateData.status === "success") {
        usernameDisplay.textContent = newUsername;
        editUsernameContainer.style.display = "none";
        editBtn.style.display = "inline-block";
        usernameStatus.textContent = "";
        alert("✅ Username updated successfully!");
      } else {
        usernameStatus.textContent = updateData.message || "❌ Failed to update username.";
        usernameStatus.style.color = "red";
      }
    });


    togglePasswordSection?.addEventListener("click", () => {
      const isVisible = passwordFormContainer.style.display === "block";
      passwordFormContainer.style.display = isVisible ? "none" : "block";
      passwordArrow.style.transform = isVisible ? "rotate(0deg)" : "rotate(180deg)";
    });


    // === DARK MODE ===
    if (localStorage.getItem("darkMode") === "enabled") {
      document.body.classList.add("dark-mode");
      darkModeToggle.checked = true;
    }

    darkModeToggle?.addEventListener("change", () => {
      if (darkModeToggle.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("darkMode", "enabled");
      } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("darkMode", "disabled");
      }
    });

    // === SETTINGS MODAL OPEN/CLOSE ===
    settingsIcon?.addEventListener("click", () => {
      settingsModal.style.display = "flex";
      blockedUsersContainer.innerHTML = `<li>Click to show blocked users...</li>`;
    });

    closeSettings?.addEventListener("click", () => {
      settingsModal.style.display = "none";
    });

    // === TOGGLE BLOCKED USERS ===
    toggleBlockedList?.addEventListener("click", async () => {
      if (blockedUsersContainer.style.display === "none") {
        blockedUsersContainer.style.display = "block";
        blockedArrow.style.transform = "rotate(180deg)";
        try {
          const res = await fetch("includes/getBlockedUsers.php");
          const data = await res.json();
          blockedUsersContainer.innerHTML = "";

          (data.users || []).forEach(user => {
            const li = document.createElement("li");
            li.innerHTML = `${user.username} <button class='unblock-btn' data-id='${user.user_id}'>Unblock</button>`;
            blockedUsersContainer.appendChild(li);
          });

          document.querySelectorAll(".unblock-btn").forEach(btn => {
            btn.addEventListener("click", async () => {
              const id = btn.dataset.id;
              const res = await fetch("includes/unblockUser.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ blocked_id: id })
              });
              const data = await res.json();
              if (data.status === "success") {
                alert("User unblocked");
                btn.parentElement.remove();
              }
            });
          });

        } catch (err) {
          blockedUsersContainer.innerHTML = "<li style='color: red;'>Failed to load users.</li>";
        }

      } else {
        blockedUsersContainer.style.display = "none";
        blockedArrow.style.transform = "rotate(0deg)";
      }
    });

    // === DELETE ACCOUNT FEATURE ===
    const deleteBtn = document.getElementById("deleteAccountBtn");
    deleteBtn?.addEventListener("click", async () => {
      const confirmDelete = confirm("Are you sure you want to permanently delete your account? This action cannot be undone!");
      if (!confirmDelete) return;

      try {
        const res = await fetch("includes/deleteAccount.php", {
          method: "POST"
        });
        const data = await res.json();

        if (data.status === "success") {
          alert("✅ Your account has been deleted.");
          window.location.href = "logout.php";
        } else {
          alert(data.message || "❌ Failed to delete account.");
        }
      } catch (err) {
        console.error("Account deletion failed", err);
        alert("⚠️ Error occurred while deleting account.");
      }
    });
    
    // === PROFILE PICTURE UPLOAD ===
    const uploadForm = document.getElementById("uploadProfileForm");
    uploadForm?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fileInput = document.getElementById("profilePicInput");
      const file = fileInput.files[0];
      if (!file) return alert("Please choose a file.");

      const formData = new FormData();
      formData.append("profile_pic", file);

      try {
        const res = await fetch("includes/uploadProfilePic.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();

        if (data.status === "success") {
          document.getElementById("profilePreview").src = data.newPath;
          alert("✅ Profile picture updated!");

        } else {
          alert(data.message || "❌ Upload failed.");
        }
      } catch (err) {
        console.error("Upload error", err);
        alert("⚠️ Error uploading file.");
      }
    });

    const profilePreview = document.getElementById("profilePreview");
    const profileModal = document.getElementById("profilePicModal");
    const profileModalImg = document.getElementById("profilePicModalImg");

    profilePreview?.addEventListener("click", () => {
      if (profileModal && profileModalImg) {
        profileModalImg.src = profilePreview.src;
        profileModal.style.display = "flex";
      }
    });

    document.querySelector("#profilePicModal .modal-blur-bg")?.addEventListener("click", () => {
      profileModal.style.display = "none";
    });

  });

  document.getElementById("updatePasswordBtn")?.addEventListener("click", async () => {
    const current = document.getElementById("currentPassword").value.trim();
    const newPass = document.getElementById("newPassword").value.trim();
    const confirm = document.getElementById("confirmPassword").value.trim();
    const status = document.getElementById("passwordStatus");

    if (!current || !newPass || !confirm) {
      status.textContent = "❗ Please fill all fields.";
      status.style.color = "red";
      return;
    }

    if (newPass !== confirm) {
      status.textContent = "❗ New passwords do not match.";
      status.style.color = "red";
      return;
    }

    const res = await fetch("includes/changePassword.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ current, newPass })
    });

    const data = await res.json();
    if (data.status === "success") {
      status.textContent = "✅ Password updated successfully!";
      status.style.color = "green";
      document.getElementById("currentPassword").value = "";
      document.getElementById("newPassword").value = "";
      document.getElementById("confirmPassword").value = "";
    } else {
      status.textContent = "❌ " + (data.message || "Failed to update password.");
      status.style.color = "red";
    }
  });


</script>

  <div id="toastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

  </body>
  </html>
