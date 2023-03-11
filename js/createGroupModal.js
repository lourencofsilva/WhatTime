// Get the modal
let createGroupModal = document.getElementById("createGroupModal");
console.log(createGroupModal);

// Get the button that opens the modal
let createGroupBtn = document.getElementById("createGroupBtn");

// Get the <span> element that closes the modal
let createGroupSpan = document.getElementsByClassName("closeCreate")[0];

// When the user clicks on the button, open the modal
createGroupBtn.onclick = () => {
  createGroupModal.style.display = "block";
};

// When the user clicks on <span> (x), close the modal
createGroupSpan.onclick = () => {
  createGroupModal.style.display = "none";
};

// When the user clicks anywhere outside of the modal, close it
window.onclick = (event) => {
  if (event.target == createGroupModal) {
    createGroupModal.style.display = "none";
  }
};

// Get the modal
let manageGroupModal = document.getElementById("manageGroupModal");

// Get the button that opens the modal
let manageGroupBtn = document.getElementById("manageGroupBtn");

// Get the <span> element that closes the modal
let manageGroupSpan = document.getElementsByClassName("closeManage")[0];

// When the user clicks on the button, open the modal
manageGroupBtn.onclick = () => {
  manageGroupModal.style.display = "block";
};

// When the user clicks on <span> (x), close the modal
manageGroupSpan.onclick = () => {
  manageGroupModal.style.display = "none";
};

// When the user clicks anywhere outside of the modal, close it
window.onclick = (event) => {
  if (event.target == manageGroupModal) {
    manageGroupModal.style.display = "none";
  }
};
