(() => {
  'use strict';

  const select = (el, all = false) => {
    el = el.trim();
    return all ? [...document.querySelectorAll(el)] : document.querySelector(el);
  };

  // Header scroll state
  const toggleScrolled = () => {
    const body = document.body;
    const header = select('#header');
    if (!header) return;
    if (window.scrollY > 40) {
      body.classList.add('scrolled');
    } else {
      body.classList.remove('scrolled');
    }
  };

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  // Mobile nav
  const mobileNavToggle = select('.mobile-nav-toggle');
  const navmenu = select('#navmenu');

  if (mobileNavToggle && navmenu) {
    mobileNavToggle.addEventListener('click', () => {
      document.body.classList.toggle('mobile-nav-active');
      navmenu.classList.toggle('mobile-nav-active');
      mobileNavToggle.classList.toggle('bi-list');
      mobileNavToggle.classList.toggle('bi-x');
    });

    select('#navmenu a', true).forEach((link) => {
      link.addEventListener('click', () => {
        if (document.body.classList.contains('mobile-nav-active')) {
          document.body.classList.remove('mobile-nav-active');
          navmenu.classList.remove('mobile-nav-active');
          mobileNavToggle.classList.add('bi-list');
          mobileNavToggle.classList.remove('bi-x');
        }
      });
    });
  }

  // Active nav on scroll
  const sections = select('section[id]', true);
  const navLinks = select('#navmenu a[href^="#"]', true);

  const setActiveNav = () => {
    const scrollY = window.scrollY + 100;
    let current = '';

    sections.forEach((section) => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      if (scrollY >= top && scrollY < top + height) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove('active');
      if (current && link.getAttribute('href') === `#${current}`) {
        link.classList.add('active');
      }
    });
  };

  document.addEventListener('scroll', setActiveNav);
  window.addEventListener('load', setActiveNav);

  // Scroll top button
  const scrollTop = select('.scroll-top');
  if (scrollTop) {
    const toggleScrollTop = () => {
      if (window.scrollY > 200) {
        scrollTop.classList.add('active');
      } else {
        scrollTop.classList.remove('active');
      }
    };
    document.addEventListener('scroll', toggleScrollTop);
    window.addEventListener('load', toggleScrollTop);
  }

  // Portfolio filter
  const filters = select('#portfolio-filters li', true);
  const items = select('.portfolio-item', true);

  filters.forEach((btn) => {
    btn.addEventListener('click', () => {
      filters.forEach((b) => b.classList.remove('filter-active'));
      btn.classList.add('filter-active');
      const filter = btn.getAttribute('data-filter');

      items.forEach((item) => {
        const cat = item.getAttribute('data-category');
        if (filter === '*' || cat === filter) {
          item.classList.remove('is-hidden');
        } else {
          item.classList.add('is-hidden');
        }
      });
    });
  });

  // Counters (About stats)
  const counters = select('.counter', true);
  let countersStarted = false;

  const animateCounters = () => {
    counters.forEach((el) => {
      const target = Number(el.getAttribute('data-target') || 0);
      const duration = 1200;
      const start = performance.now();

      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        el.textContent = String(Math.floor(progress * target));
        if (progress < 1) {
          requestAnimationFrame(tick);
        } else {
          el.textContent = String(target);
        }
      };

      requestAnimationFrame(tick);
    });
  };

  const about = select('#about');
  if (about && counters.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !countersStarted) {
            countersStarted = true;
            animateCounters();
            observer.disconnect();
          }
        });
      },
      { threshold: 0.35 }
    );
    observer.observe(about);
  }
})();
