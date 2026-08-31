/* Sweet Mart – Main JS */
document.addEventListener('DOMContentLoaded',function(){
  // Animate on scroll
  const obs=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('visible');}});},{threshold:0.1});
  document.querySelectorAll('.animate-on-scroll').forEach(el=>{el.classList.add('anim-hidden');obs.observe(el);});

  // Cart AJAX add
  document.querySelectorAll('.btn-add-cart').forEach(btn=>{
    btn.addEventListener('click',function(e){
      e.preventDefault();
      const pid=this.dataset.id;
      const qty=this.dataset.qty||1;
      if(!pid)return;
      const orig=this.innerHTML;
      this.innerHTML='<i class="bi bi-check"></i> Added!';
      this.style.background='#5a9a6a';
      fetch('cart_action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=add&product_id=${pid}&qty=${qty}`})
        .then(r=>r.json()).then(d=>{
          const badge=document.getElementById('cart-count');
          if(badge&&d.count!==undefined)badge.textContent=d.count;
          setTimeout(()=>{this.innerHTML=orig;this.style.background='';},1800);
        }).catch(()=>{setTimeout(()=>{this.innerHTML=orig;this.style.background='';},1800);});
    });
  });

  // Qty controls in cart
  document.querySelectorAll('.qty-btn').forEach(btn=>{
    btn.addEventListener('click',function(){
      const inp=this.closest('.qty-control').querySelector('.qty-input');
      let v=parseInt(inp.value)||1;
      if(this.dataset.dir==='up')v=Math.min(v+1,99);
      else v=Math.max(v-1,1);
      inp.value=v;
      inp.dispatchEvent(new Event('change'));
    });
  });

  // Flash auto-hide
  const flash=document.querySelector('.alert');
  if(flash)setTimeout(()=>{flash.style.opacity='0';flash.style.transition='opacity .5s';setTimeout(()=>flash.remove(),500);},4000);

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',function(e){
      const t=document.querySelector(this.getAttribute('href'));
      if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth'});}
    });
  });

  // Admin sidebar toggle
  const sidebarOpen=document.getElementById('sidebarOpen');
  const sidebar=document.getElementById('adminSidebar');
  const sidebarClose=document.getElementById('sidebarClose');
  if(sidebarOpen)sidebarOpen.addEventListener('click',()=>sidebar.classList.add('open'));
  if(sidebarClose)sidebarClose.addEventListener('click',()=>sidebar.classList.remove('open'));

  // Animate stats count up
  document.querySelectorAll('.stat-value[data-count]').forEach(el=>{
    const target=parseInt(el.dataset.count)||0;
    let cur=0;const step=Math.ceil(target/40);
    const iv=setInterval(()=>{cur=Math.min(cur+step,target);el.textContent=cur.toLocaleString();if(cur>=target)clearInterval(iv);},30);
  });
});
// Anim CSS
const style=document.createElement('style');
style.textContent='.anim-hidden{opacity:0;transform:translateY(20px);transition:opacity .5s ease,transform .5s ease;}.visible{opacity:1!important;transform:none!important;}';
document.head.appendChild(style);
