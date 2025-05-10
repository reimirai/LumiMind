const toggleBtn = document.getElementById('toggleBtn');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

menuitem.addEventListener('click', () => {
  sidebar.classList.toggle('hidden');

  if (sidebar.classList.contains('hidden')) {
    mainContent.style.marginLeft = '0px';
  } else {
    mainContent.style.marginLeft = '240px'; 
  }
});
