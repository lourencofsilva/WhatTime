// Get the modal
let manageGroupModal = document.getElementById("manageGroupModal");

// Get the button that opens the modal
let manageGroupBtn = document.getElementById("manageGroupBtn");

// Get the <span> element that closes the modal
let manageGroupSpan = document.getElementsByClassName("closeManage")[0];

let mainBody = document.getElementsByTagName("BODY")[0];


// When the user clicks on the button, open the modal
manageGroupBtn.onclick = () => {
  manageGroupModal.style.display = "block";
  manageGroupModal.style.overflow = 'hidden';
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


