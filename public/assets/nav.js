(function(){
  const toggle = document.querySelector('.nav-toggle');
  const menu = document.getElementById('nav-menu');

  function closeMenu(){
    if(!toggle) return;
    toggle.setAttribute('aria-expanded','false');
    menu.classList.remove('show');
  }

  function openMenu(){
    if(!toggle) return;
    toggle.setAttribute('aria-expanded','true');
    menu.classList.add('show');
  }

  function toggleMenu(){
    if(!toggle) return;
    const expanded = toggle.getAttribute('aria-expanded') === 'true';
    if(expanded) closeMenu(); else openMenu();
  }

  if(toggle){
    toggle.addEventListener('click', function(e){
      e.preventDefault();
      toggleMenu();
    });
  }

  // close when any link inside menu is clicked
  if(menu){
    menu.addEventListener('click', function(e){
      const t = e.target.closest('a');
      if(t) closeMenu();
    });
  }

  // also close on custom event (used in markup)
  window.addEventListener('nav:close', closeMenu);

  // close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (menu && menu.classList.contains('show')) {
      const clickedInMenu = menu.contains(e.target);
      const clickedToggle = toggle.contains(e.target);
      if (!clickedInMenu && !clickedToggle) {
        closeMenu();
      }
    }
  });
})();