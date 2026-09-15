/* Sweet Mart – Custom Box Builder */
const BOX_PRICES={Small:800,Medium:1400,Large:2200};
const DESSERT_PRICES={Brownies:220,Cupcakes:280,Cookies:160,Donuts:180,Chocolates:150};
const BOX_MAX={Small:4,Medium:8,Large:12};
let state={step:1,size:'',desserts:{},occasion:'',message:'',totalItems:0};

function goStep(n){
  if(n===2&&!state.size){alert('Please choose a box size first.');return;}
  if(n===3&&state.totalItems===0){alert('Please add at least one dessert.');return;}
  if(n===4&&!state.occasion){alert('Please choose an occasion.');return;}
  if(n>5||n<1)return;
  document.querySelectorAll('.step-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.box-step').forEach((s,i)=>{
    s.classList.remove('active','done');
    if(i+1<n)s.classList.add('done');
    if(i+1===n)s.classList.add('active');
  });
  const panel=document.getElementById('step'+n);
  if(panel)panel.classList.add('active');
  state.step=n;
  if(n===5)renderReview();
}

function selectSize(sz){
  state.size=sz;state.desserts={};state.totalItems=0;
  document.querySelectorAll('.size-option').forEach(el=>el.classList.remove('selected'));
  const el=document.querySelector(`.size-option[data-size="${sz}"]`);
  if(el)el.classList.add('selected');
  document.getElementById('max-items-note').textContent=`You can add up to ${BOX_MAX[sz]} items.`;
  renderDessertItems();
  updatePrice();
}

function renderDessertItems(){
  const wrap=document.getElementById('dessert-items-wrap');
  if(!wrap)return;
  const max=BOX_MAX[state.size]||4;
  wrap.innerHTML=Object.entries(DESSERT_PRICES).map(([name,price])=>{
    const qty=state.desserts[name]||0;
    return `<div class="col-md-6"><div class="dessert-item ${qty>0?'selected':''}" id="di-${name}">
      <div><div style="font-weight:600">${name}</div><div style="font-size:.8rem;color:#8a6a5a">LKR ${price} each</div></div>
      <div class="qty-control">
        <button class="qty-btn" onclick="changeDessert('${name}',-1)">−</button>
        <span id="dqty-${name}" style="min-width:24px;text-align:center;font-weight:600">${qty}</span>
        <button class="qty-btn" onclick="changeDessert('${name}',1)">+</button>
      </div></div></div>`;
  }).join('');
}

function changeDessert(name,delta){
  const max=BOX_MAX[state.size]||4;
  const cur=state.desserts[name]||0;
  const newQty=Math.max(0,cur+delta);
  const total=Object.values(state.desserts).reduce((a,b)=>a+b,0)-(cur)+(newQty);
  if(total>max){alert(`Maximum ${max} items for ${state.size} box.`);return;}
  if(newQty===0)delete state.desserts[name];else state.desserts[name]=newQty;
  state.totalItems=Object.values(state.desserts).reduce((a,b)=>a+b,0);
  const qEl=document.getElementById('dqty-'+name);if(qEl)qEl.textContent=newQty;
  const diEl=document.getElementById('di-'+name);if(diEl)diEl.classList.toggle('selected',newQty>0);
  updatePrice();
}

function selectOccasion(occ){
  state.occasion=occ;
  document.querySelectorAll('.occasion-option').forEach(el=>el.classList.remove('selected'));
  const el=document.querySelector(`.occasion-option[data-occ="${occ}"]`);
  if(el)el.classList.add('selected');
}

function updatePrice(){
  const base=BOX_PRICES[state.size]||0;
  const extras=Object.entries(state.desserts).reduce((sum,[name,qty])=>sum+(DESSERT_PRICES[name]||0)*qty,0);
  const total=base+extras;
  const el=document.getElementById('box-price-preview');
  if(el)el.textContent='LKR '+total.toLocaleString('en-LK',{minimumFractionDigits:2});
  return total;
}

function renderReview(){
  const total=updatePrice();
  const el=document.getElementById('review-content');
  if(!el)return;
  const items=Object.entries(state.desserts).map(([n,q])=>`<li>${n} × ${q} — LKR ${(DESSERT_PRICES[n]*q).toLocaleString()}</li>`).join('');
  el.innerHTML=`<div class="review-row"><b>Box Size:</b> ${state.size} (LKR ${BOX_PRICES[state.size]})</div>
    <div class="review-row"><b>Desserts:</b><ul>${items||'<li>None selected</li>'}</ul></div>
    <div class="review-row"><b>Occasion:</b> ${state.occasion}</div>
    <div class="review-row"><b>Message:</b> ${state.message||'—'}</div>
    <div class="review-row" style="font-size:1.2rem;font-weight:700;color:var(--brown-dark)"><b>Total: LKR ${total.toLocaleString('en-LK',{minimumFractionDigits:2})}</b></div>`;
  document.getElementById('box-state-input').value=JSON.stringify(state);
}

document.addEventListener('DOMContentLoaded',function(){
  const msgEl=document.getElementById('box-message');
  if(msgEl)msgEl.addEventListener('input',function(){state.message=this.value;});
  goStep(1);
});
