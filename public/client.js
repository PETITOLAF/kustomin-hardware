async function getJSON(path){const r=await fetch(path);return r.json()}
let pricesData = null
async function init(){
  pricesData = await getJSON('/data/prices.json')
  const cpu = document.getElementById('cpu')
  const mobo = document.getElementById('mobo')
  const ram = document.getElementById('ram')
  const gpu = document.getElementById('gpu')
  const premadeGrid = document.getElementById('premadeGrid')

  ['','Intel i5','Intel i7','Ryzen 5','Ryzen 7'].forEach(c=>{let o=new Option(c,c);cpu.add(o)})
  ['','Intel LGA1200','Intel LGA1700','AM4','AM5'].forEach(m=>{let o=new Option(m,m);mobo.add(o)})
  ['','DDR4 16Go','DDR5 16Go'].forEach(r=>{let o=new Option(r,r);ram.add(o)})
  ['','RTX 4060','RTX 4070'].forEach(g=>{let o=new Option(g,g);gpu.add(o)})

  const premades = pricesData.premades
  for(const name in premades){
    const card = document.createElement('div');card.className='card';
    const html = `<h3>${name}</h3><p>Composants: ${premades[name].components.join(', ')}</p><p><strong id='price-${name.replace(/\s/g,'_')}'>Calcul en cours...</strong></p><div style='margin-top:8px'><button onclick="buyPremade('${name}')">Commander</button></div>`
    card.innerHTML = html
    premadeGrid.appendChild(card)
    calcPriceForComponents(premades[name].components).then(p=>{document.getElementById('price-'+name.replace(/\s/g,'_')).innerText = p+' € (TTC)'}).catch(()=>{})
  }

  document.getElementById('checkBtn').addEventListener('click',checkCompat)
  document.getElementById('priceBtn').addEventListener('click',onBuyConfig)
}

async function calcPriceForComponents(list){
  const res = await fetch('/api/price.php',{
    method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({components:list})
  })
  const j = await res.json()
  if(res.ok) return j.total.toFixed(2)
  throw new Error('Erreur calcul prix')
}

function checkCompat(){
  const cpu = document.getElementById('cpu').value
  const mobo = document.getElementById('mobo').value
  const ram = document.getElementById('ram').value
  const errors=[]
  if(cpu.includes('Intel') && mobo.startsWith('AM')) errors.push('CPU Intel incompatible avec carte mère AMD.')
  if(cpu.includes('Ryzen') && mobo.startsWith('Intel')) errors.push('CPU AMD incompatible avec carte mère Intel.')
  if(ram.includes('DDR5') && mobo==='AM4') errors.push('DDR5 incompatible avec AM4.')
  document.getElementById('compatResult').innerText=errors.length?errors.join(' '):'✔ Configuration compatible !'
}

async function onBuyConfig(){
  const comps=[document.getElementById('cpu').value, document.getElementById('mobo').value, document.getElementById('ram').value, document.getElementById('gpu').value]
  const email=prompt('Votre email pour la facture (optionnel) :')
  const res=await fetch('/api/checkout.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({components:comps,customerEmail:email})})
  const j=await res.json()
  if(res.ok && j.url){ window.location.href = j.url }
  else if(res.ok && j.success){ alert('Commande envoyée : ' + j.message) }
  else { alert('Erreur : '+(j.error||'Impossible de créer la commande')) }
}

async function buyPremade(name){
  const comps=(await getJSON('/data/prices.json')).premades[name].components
  const email=prompt('Votre email (optionnel) :')
  const res=await fetch('/api/checkout.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({components:comps,customerEmail:email})})
  const j=await res.json()
  if(res.ok && j.url) window.location.href = j.url
  else if(res.ok && j.success) alert('Commande envoyée : '+j.message)
  else alert('Erreur: '+(j.error||''))
}

window.addEventListener('DOMContentLoaded',init)
