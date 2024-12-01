// --- UPLOAD ---
const uploadArea = document.getElementById("upload-area");
const fileInput = document.getElementById("file");
const fileLabel = document.getElementById("file-label");

// Prevent default browser behavior for drag-and-drop
["dragenter", "dragover", "dragleave", "drop"].forEach(eventType => {
  uploadArea.addEventListener(eventType, e => e.preventDefault());
  uploadArea.addEventListener(eventType, e => e.stopPropagation());
});

// Highlight upload area when file is dragged over
uploadArea.addEventListener("dragover", () => {
  uploadArea.classList.add("dragover");
});

// Remove highlight when drag leaves or files are dropped
["dragleave", "drop"].forEach(eventType => {
  uploadArea.addEventListener(eventType, () => {
    uploadArea.classList.remove("dragover");
  });
});

// Handle dropped files
uploadArea.addEventListener("drop", e => {
  const files = e.dataTransfer.files;
  if (files.length) {
    fileInput.files = files; // Set dropped files to input
    fileLabel.textContent = `File selected: ${files[0].name}`;
  }
});

// Trigger file input click when clicking the upload area or label
uploadArea.addEventListener("click", () => fileInput.click());
fileLabel.addEventListener("click", () => fileInput.click());

// Update label when a file is selected through input
fileInput.addEventListener("change", () => {
  if (fileInput.files.length) {
    fileLabel.textContent = `File selected: ${fileInput.files[0].name}`;
  }
});