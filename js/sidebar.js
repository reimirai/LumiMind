document.querySelectorAll('.menu-heading').forEach(function(menuHeading) {
  menuHeading.addEventListener('click', function () {
    const parent = this.closest('.nav-item');
    const submenu = parent.querySelector('.submenu');

    if (submenu) {
      // Toggle submenu visibility
      const isVisible = submenu.style.display === 'block';
      submenu.style.display = isVisible ? 'none' : 'block';
    }
  });
});