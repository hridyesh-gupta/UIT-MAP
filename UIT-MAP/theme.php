<?php
// Included file: provides site-wide theme variables, hover animations, and dark-accent background
?>
<style>
  /* Core theme variables */
  :root{
    --bg: #ffffff;
    --text: #0f172a;
    --muted: #6b7280;
    --accent: #3b82f6;
    --accent-2: #2563eb;
    --card-bg: rgba(255,255,255,0.9);
    --glass: rgba(255,255,255,0.6);
    --control-bg: #f8fafc;
    --focus-ring: 0 8px 20px rgba(59,130,246,0.12);
  }
  [data-theme="dark"]{
    --bg: #071025;
    --text: #e6eef8;
    --muted: #94a3b8;
    --accent: #60a5fa;
    --accent-2: #1e3a8a;
    --card-bg: rgba(7,16,37,0.78);
    --glass: rgba(11,18,32,0.6);
    --control-bg: rgba(255,255,255,0.04);
    --focus-ring: 0 10px 30px rgba(96,165,250,0.14);
  }

  /* Page and container */
  html,body{background-color:var(--bg); color:var(--text); transition:background-color 240ms ease, color 240ms ease;}

  /* Global animated link and button behaviour */
  a, button, input[type="submit"], .heyform__trigger-button{
    transition: transform 160ms cubic-bezier(.2,.8,.2,1), box-shadow 180ms, color 140ms, background-color 160ms;
    will-change: transform, box-shadow;
  }
  a:hover, button:hover, input[type="submit"]:hover, .heyform__trigger-button:hover{
    transform: translateY(-4px) scale(1.01);
    box-shadow: var(--focus-ring);
  }
  a:active, button:active, input[type="submit"]:active{ transform: translateY(-1px) scale(0.998); }

  /* Inputs: email/password and controls */
  input[type="text"], input[type="password"], input[type="email"], textarea, select{
    background: var(--control-bg);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    outline: none;
    transition: box-shadow 180ms, transform 160ms, border-color 160ms, background-color 160ms;
  }
  input[type="text"]:hover, input[type="password"]:hover, textarea:hover{ transform: translateY(-2px); }
  input:focus, textarea:focus, select:focus{
    box-shadow: var(--focus-ring);
    border-color: var(--accent);
    transform: translateY(-2px) scale(1.002);
  }

  /* Show password icon/button */
  #togglePassword{ cursor: pointer; transition: transform 140ms, color 140ms; }
  #togglePassword:hover{ transform: rotate(-12deg) scale(1.08); color: var(--accent); }

  /* Radios and checkboxes */
  input[type="radio"], input[type="checkbox"]{
    width:18px; height:18px; margin:0 6px 0 0; vertical-align:middle; transition: transform 140ms, box-shadow 140ms;
  }
  input[type="radio"]:hover, input[type="checkbox"]:hover{ transform: scale(1.08); }
  input[type="radio"]:checked, input[type="checkbox"]:checked{ box-shadow: var(--focus-ring); }

  /* Images used as background: subtle scale and hue shift on hover/active */
  body::before{
    content: ""; position: fixed; inset:0; z-index:-2; background-image: url('img/uit.png'); background-size: cover; background-position:center; opacity:0.18; transition: transform 600ms ease, filter 360ms ease, opacity 260ms;
    transform-origin:center;
    pointer-events:none;
  }
  [data-theme="dark"] body::before{ filter: grayscale(10%) brightness(0.45) contrast(1.05) saturate(1.2) sepia(0.05); opacity:0.32; }
  body:hover::before{ transform: scale(1.02) translateY(-6px); opacity:0.24; }
  body:active::before{ transform: scale(0.995); }

  /* Dialogue / card (main login box) */
  .card, .bg-white, .bg-opacity-80{ background: var(--card-bg) !important; box-shadow: 0 8px 30px rgba(2,6,23,0.12); border-radius:12px; transition: background 240ms, transform 220ms, box-shadow 220ms; }
  .card:hover{ transform: translateY(-6px); box-shadow: 0 18px 50px rgba(2,6,23,0.16); }
  [data-theme="dark"] .card{ border: 1px solid rgba(255,255,255,0.04); }

  /* Accent background for dark mode: gradient + photo overlay */
  .dark-accent-hero{
    background: linear-gradient(180deg, rgba(6,11,38,0.6), rgba(3,6,20,0.7)), url('img/uit.png');
    background-size: cover; background-position:center; border-radius:12px; overflow:hidden; position:relative; color:var(--text);
  }
  [data-theme="dark"] .dark-accent-hero{ box-shadow: inset 0 0 120px rgba(0,0,0,0.35); }
  .dark-accent-hero::after{ content:""; position:absolute; inset:0; background: linear-gradient(120deg, rgba(96,165,250,0.06), rgba(29,78,216,0.08)); mix-blend-mode: overlay; pointer-events:none; }

  /* Small helpers for focused items */
  .focus-outline{ box-shadow: var(--focus-ring) !important; }

  /* Make icons and inline svgs animate on hover */
  svg{ transition: transform 160ms, opacity 140ms; }
  svg:hover{ transform: translateY(-3px) rotate(-6deg); }

  /* Reduce motion for users who prefer reduced motion */
  @media (prefers-reduced-motion: reduce){
    *{ transition: none !important; animation: none !important; }
  }

</style>

<script>
  // Add event delegation for inputs to add a tiny ripple/focus class on focus & hover for dynamic elements
  (function(){
    function onFocus(e){ e.target.classList.add('focus-outline'); }
    function onBlur(e){ e.target.classList.remove('focus-outline'); }
    ['focus','blur'].forEach(ev => document.addEventListener(ev, ev === 'focus' ? onFocus : onBlur, true));

    // Enhance checkboxes/radios keyboard toggle animation
    document.addEventListener('keydown', function(e){
      if((e.key === ' ' || e.key === 'Enter') && document.activeElement && (document.activeElement.type === 'checkbox' || document.activeElement.type === 'radio')){
        document.activeElement.classList.add('focus-outline');
        setTimeout(()=> document.activeElement.classList.remove('focus-outline'), 180);
      }
    });

    // Add hover lift to images inside cards
    document.addEventListener('mouseover', function(e){
      const img = e.target.closest('img');
      if(img){ img.style.transition = 'transform 360ms ease, filter 320ms'; img.style.transform = 'translateY(-6px) scale(1.03)'; img.style.filter = 'drop-shadow(0 10px 30px rgba(0,0,0,0.22))'; }
    });
    document.addEventListener('mouseout', function(e){
      const img = e.target.closest('img'); if(img){ img.style.transform = ''; img.style.filter = ''; }
    });
  })();
</script>
<!-- Onboarding (Shepherd.js) scaffold -->
<link rel="stylesheet" href="https://unpkg.com/shepherd.js/dist/css/shepherd.css" />
<script src="https://unpkg.com/shepherd.js/dist/js/shepherd.min.js" defer></script>
<script>
  // startGuidedTour(): basic tour for admin pages
  function startGuidedTour(){
    if(typeof Shepherd === 'undefined'){
      showToast('Tour library not loaded', 'error', 2500); return;
    }
    const tour = new Shepherd.Tour({useModalOverlay:true,defaultStepOptions:{cancelIcon:{enabled:true},scrollTo:true}});
    tour.addStep({title:'Navigation', text:'Use this navigation to move between sections.', attachTo:{element:'nav',on:'bottom'}});
    tour.addStep({title:'Groups list', text:'Search and manage groups here.', attachTo:{element:'#searchInput',on:'bottom'}});
    tour.addStep({title:'Theme & Quick actions', text:'Use theme toggle and picker to change appearance anytime.', attachTo:{element:'#theme-toggle',on:'left'}});
    tour.start();
  }
</script>

<!-- Toast / Snackbar markup (injected by theme include) -->
<div id="uit-toast" aria-live="polite" style="position:fixed;right:20px;bottom:20px;z-index:99999;pointer-events:none;">
  <!-- toasts appended here -->
</div>

<script>
  // Simple toast utility: showToast(message, type='info', duration=3500)
  function showToast(message, type='info', duration=3500){
    const container = document.getElementById('uit-toast');
    if(!container) return;
    const toast = document.createElement('div');
    toast.setAttribute('role','status');
    toast.setAttribute('aria-atomic','true');
    toast.style.pointerEvents = 'auto';
    toast.style.marginTop = '8px';
    toast.style.background = type === 'error' ? 'linear-gradient(90deg,#ff7a7a,#ff4d4d)' : 'linear-gradient(90deg,#60a5fa,#2563eb)';
    toast.style.color = 'white';
    toast.style.padding = '10px 14px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 10px 30px rgba(2,6,23,0.18)';
    toast.style.transform = 'translateY(12px)';
    toast.style.opacity = '0';
    toast.style.transition = 'transform 320ms ease, opacity 320ms ease';
    toast.textContent = message;
    container.appendChild(toast);
    requestAnimationFrame(()=>{ toast.style.transform = 'translateY(0)'; toast.style.opacity = '1'; });
    setTimeout(()=>{
      toast.style.transform = 'translateY(12px)'; toast.style.opacity = '0';
      setTimeout(()=> container.removeChild(toast), 360);
    }, duration);
  }

  // Skeleton helper: add `.skeleton` class to elements to show placeholder, call removeSkeleton(container) to remove
  (function(){
    const style = document.createElement('style');
    style.textContent = `
      .skeleton{ position:relative; overflow:hidden; background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.06), rgba(255,255,255,0.03)); }
      .skeleton::after{ content:''; position:absolute; inset:0; transform:translateX(-100%); background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.06), rgba(255,255,255,0.02)); animation: skeleton-shimmer 1.4s infinite; }
      @keyframes skeleton-shimmer{ 100%{ transform:translateX(100%);} }
    `;
    document.head.appendChild(style);
    window.removeSkeleton = function(container){ if(!container) return; container.querySelectorAll('.skeleton').forEach(el=> el.classList.remove('skeleton')); };
  })();
</script>

    <!-- Upload progress helper -->
    <script>
      // showUploadProgress(containerElement, percent) - shows a small progress bar inside containerElement
      function showUploadProgress(container, percent){
        if(!container) return;
        let bar = container.querySelector('.uit-upload-progress');
        if(!bar){
          bar = document.createElement('div');
          bar.className = 'uit-upload-progress';
          bar.style.position = 'relative';
          bar.style.height = '8px';
          bar.style.background = 'rgba(255,255,255,0.06)';
          bar.style.borderRadius = '6px';
          bar.style.overflow = 'hidden';
          bar.innerHTML = '<div class="uit-upload-fill" style="height:100%;width:0%;background:linear-gradient(90deg,var(--accent),var(--accent-2));transition:width 220ms;"></div>';
          container.appendChild(bar);
        }
        const fill = bar.querySelector('.uit-upload-fill');
        const pct = Math.max(0, Math.min(100, Math.round(percent)));
        fill.style.width = pct + '%';
        if(pct >= 100){ setTimeout(()=>{ if(bar && bar.parentNode) bar.parentNode.removeChild(bar); }, 600); }
      }

      // Simple search helper: call attachSearch(input, table) to filter rows by text
      function attachSearch(input, table){
        if(!input || !table) return;
        input.addEventListener('input', ()=>{
          const q = input.value.trim().toLowerCase();
          Array.from(table.tBodies[0].rows).forEach(row=>{
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
          });
        });
      }

      // Theme picker UI
      (function(){
        const accents = [ ['#3b82f6','#2563eb'], ['#60a5fa','#1e3a8a'], ['#f97316','#ea580c'], ['#a78bfa','#7c3aed'] ];
        const container = document.createElement('div');
        container.style.position = 'fixed'; container.style.left = '18px'; container.style.bottom = '18px'; container.style.zIndex='99999';
        container.innerHTML = '<button id="uit-theme-picker-btn" aria-label="Theme accents" style="background:transparent;border:1px solid rgba(0,0,0,0.08);padding:6px;border-radius:8px;color:var(--text);">🎨</button>';
        document.body.appendChild(container);
        const panel = document.createElement('div'); panel.style.display='none'; panel.style.marginTop='8px'; panel.style.padding='10px'; panel.style.background='var(--card-bg)'; panel.style.borderRadius='8px'; panel.style.boxShadow='0 8px 30px rgba(2,6,23,0.12)';
        accents.forEach((pair, idx)=>{
          const b = document.createElement('button'); b.style.width='34px'; b.style.height='34px'; b.style.borderRadius='8px'; b.style.margin='6px'; b.style.border='none'; b.style.cursor='pointer'; b.style.outline='none'; b.style.background = `linear-gradient(90deg, ${pair[0]}, ${pair[1]})`;
          b.setAttribute('data-idx', idx);
          b.addEventListener('click', ()=>{ document.documentElement.style.setProperty('--accent', pair[0]); document.documentElement.style.setProperty('--accent-2', pair[1]); localStorage.setItem('uit-accent', JSON.stringify(pair)); showToast('Accent updated', 'info', 1500); });
          panel.appendChild(b);
        });
        container.appendChild(panel);
        document.getElementById('uit-theme-picker-btn').addEventListener('click', ()=>{ panel.style.display = panel.style.display === 'none' ? 'grid' : 'none'; panel.style.gridTemplateColumns = 'repeat(4, auto)'; });
        // load saved accent
        const saved = localStorage.getItem('uit-accent');
        if(saved){ try{ const p = JSON.parse(saved); document.documentElement.style.setProperty('--accent', p[0]); document.documentElement.style.setProperty('--accent-2', p[1]); }catch(e){} }
      })();
    </script>
