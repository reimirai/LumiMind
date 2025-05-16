function toggleList(listId) {
    const list = document.getElementById(listId);
    const button = list.previousElementSibling.querySelector("button i");
  
    if (list.style.display === "none") {
      list.style.display = "block";
      button.classList.remove("fa-chevron-down");
      button.classList.add("fa-chevron-up");
    } else {
      list.style.display = "none";
      button.classList.remove("fa-chevron-up");
      button.classList.add("fa-chevron-down");
    }
  }
  
 
    function toggleList(listId, btn) {
        const list = document.getElementById(listId);
        const img = btn.querySelector("img");

        if (list.style.display === "none" || list.style.display === "") {
            list.style.display = "block";
            img.src = "../icon/Chevronup.png";
        } else {
            list.style.display = "none";
            img.src = "../icon/Chevrondown.png";
        }
    }

    function toggleActions(card) {
      const wrapper = card.closest('.note-wrapper');
      wrapper.classList.toggle('show-actions');
    }



