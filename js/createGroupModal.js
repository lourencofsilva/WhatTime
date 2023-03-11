// Get the modal
let modal = document.getElementById("createGroupModal");

// Get the button that opens the modal
let btn = document.getElementById("createGroupBtn");

// Get the <span> element that closes the modal
let span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
btn.onclick = () => {
  modal.style.display = "block";
};

// When the user clicks on <span> (x), close the modal
span.onclick = () => {
  modal.style.display = "none";
};

// When the user clicks anywhere outside of the modal, close it
window.onclick = (event) => {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};
