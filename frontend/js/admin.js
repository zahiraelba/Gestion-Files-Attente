const API="api.php";
let S={services:[],guichets:[],tickets:[],servis:0,heures:[]};
const $=id=>document.getElementById(id);
const esc=s=>String(s).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]));

async function call(action,data){
  const opt=data?{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(data)}:{};
  const r=await fetch(API+"?action="+action,opt);
  const j=await r.json();
  if(!r.ok)throw new Error(j.error||"Erreur serveur");
  return j;
}
async function load(){
  try{S=await call("state");render();}
  catch(e){$("clock").textContent="Connexion impossible : "+e.message;}
}
async function act(action,data){
  try{await call(action,data);}catch(e){alert(e.message);}
  load();
}

function render(){
  const wait=S.tickets.filter(t=>t.statut==="attente");
  if(!$("svcSel").options.length)$("svcSel").innerHTML=S.services.map(s=>`<option value="${s.id}">${esc(s.nom)}</option>`).join("");

  $("board").innerHTML=S.guichets.map(c=>{
    const lbl={libre:"Libre",pause:"En pause",actif:"En service"}[c.statut];
    return `<article class="g ${c.statut}"><div class="top"><span>Guichet ${c.id} · ${esc(c.service)}</span><span>${lbl}</span></div>
    <div class="num">${c.ticket?esc(c.ticket):(c.statut==="pause"?"Pause":"—")}</div>
    <div class="sub">${esc(c.agent)}</div>
    <div class="acts"><button class="btn sm" data-a="next" data-i="${c.id}" ${c.statut==="pause"?"disabled":""}>Appeler le suivant</button>
    <button class="btn sm ghost" data-a="finish" data-i="${c.id}" ${c.ticket?"":"disabled"}>Terminer</button>
    <button class="btn sm ghost" data-a="pause" data-i="${c.id}">${c.statut==="pause"?"Reprendre":"Pause"}</button></div></article>`}).join("");

  const active=S.guichets.filter(c=>c.statut!=="pause").length;
  const avg=wait.length?Math.round(wait.reduce((a,t)=>a+ +t.minutes,0)/wait.length):0;
  $("kpis").innerHTML=[[wait.length,"Clients en attente"],[avg+" min","Attente moyenne actuelle"],[S.servis,"Tickets servis aujourd’hui"],[active+" / "+S.guichets.length,"Guichets ouverts"]].map(([v,l])=>`<div class="k"><b>${v}</b><span>${l}</span></div>`).join("");

  $("rows").innerHTML=S.tickets.length?S.tickets.map(t=>{const m=+t.minutes;
    const tag=t.statut==="appel"?`<span class="tag t-appel">Appelé</span>`:m>=15?`<span class="tag t-long">Attente longue</span>`:`<span class="tag t-attente">En attente</span>`;
    return `<tr><td class="tk">${esc(t.numero)}</td><td>${esc(t.service)}</td><td>${m} min</td><td>${tag}</td></tr>`}).join(""):`<tr><td colspan="4" class="empty">Aucun client en attente. Ajoutez un ticket pour commencer.</td></tr>`;

  const data=S.heures,max=Math.max(1,...data),w=34;
  $("chart").innerHTML=`<svg viewBox="0 0 330 150" width="100%" role="img" aria-label="Tickets servis par heure">`+data.map((v,i)=>{const h=Math.round(v/max*100);return `<rect class="bar" x="${i*w+6}" y="${110-h}" width="24" height="${h}" rx="4"/><text x="${i*w+18}" y="${104-h}" text-anchor="middle">${v}</text><text x="${i*w+18}" y="130" text-anchor="middle">${8+i}h</text>`}).join("")+`</svg>`;

  $("svc").innerHTML=S.services.map(s=>{const n=wait.filter(t=>t.service===s.nom).length;return `<div><span>${esc(s.nom)}</span><span class="trk"><i style="width:${Math.min(100,n*20)}%"></i></span><b>${n}</b></div>`}).join("");
}

$("board").addEventListener("click",e=>{const b=e.target.closest("button");if(!b||b.disabled)return;act(b.dataset.a,{guichet_id:+b.dataset.i});});
$("add").onclick=()=>act("add",{service_id:+$("svcSel").value});
$("theme").onclick=()=>{const d=document.documentElement,dark=d.dataset.theme==="dark"||(!d.dataset.theme&&matchMedia("(prefers-color-scheme:dark)").matches);d.dataset.theme=dark?"light":"dark";$("theme").textContent=dark?"Mode sombre":"Mode clair";};
document.querySelectorAll(".side button").forEach(b=>b.onclick=()=>{document.querySelectorAll(".side button").forEach(x=>x.classList.remove("on"));b.classList.add("on");});
function tick(){$("clock").textContent=new Date().toLocaleDateString("fr-FR",{weekday:"long",day:"numeric",month:"long"})+" · "+new Date().toLocaleTimeString("fr-FR",{hour:"2-digit",minute:"2-digit"});}
tick();load();
setInterval(()=>{tick();load();},5000);