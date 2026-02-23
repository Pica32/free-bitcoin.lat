import"./hoisted.COZWcr4e.js";const u=5*60*1e3;function i(c){return`btc_price_${c.toLowerCase()}`}function p(c){try{const e=localStorage.getItem(i(c));if(!e)return null;const n=JSON.parse(e);return Date.now()-n.fetchedAt>u*2?(localStorage.removeItem(i(c)),null):n}catch{return null}}function f(c,e){try{localStorage.setItem(i(c),JSON.stringify(e))}catch{}}function m(c){return Date.now()-c.fetchedAt>u}async function g(c){const e=await fetch(`/api/price.php?currency=${encodeURIComponent(c)}`,{headers:{Accept:"application/json"},signal:AbortSignal.timeout(4e3)});if(!e.ok)throw new Error(`Own API HTTP ${e.status}`);const n=await e.json();return{usd:n.usd??0,local:n.local??0,change24h:n.change24h??0,fetchedAt:Date.now()}}async function w(c){const e=c.toLowerCase(),n=`https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd,${e}&include_24hr_change=true`,t=await fetch(n,{headers:{Accept:"application/json"},signal:AbortSignal.timeout(6e3)});if(!t.ok)throw new Error(`CoinGecko HTTP ${t.status}`);const a=(await t.json()).bitcoin;return{usd:a.usd??0,local:a[e]??0,change24h:a[`${e}_24h_change`]??a.usd_24h_change??0,fetchedAt:Date.now()}}function s(c){const e=Math.floor((Date.now()-c)/6e4);return e<1?"hace menos de 1 min":e===1?"hace 1 min":`hace ${e} min`}function o(c,e=2){return c.toLocaleString("es-419",{minimumFractionDigits:e,maximumFractionDigits:e})}function l(c,e,n,t){const r=e.change24h>=0?"change-up":"change-down",a=e.change24h>=0?"+":"",d=e.local>1e4?0:2;c.innerHTML=`
      <div class="btc-symbol" aria-hidden="true">₿</div>
      <div class="price-main">
        <div class="price-value-row">
          <span class="price-value price-value-local">${n} ${o(e.local,d)}</span>
          <span class="price-currency-tag">${t}</span>
        </div>
        <p class="price-usd-row">≈ $${o(e.usd,0)} USD</p>
      </div>
      <div class="price-secondary">
        <div class="price-meta-row">
          <span class="price-change ${r}" aria-label="Cambio en 24 horas: ${a}${o(e.change24h)}%">
            ${a}${o(e.change24h)}% (24h)
          </span>
          <span class="price-updated" aria-label="Precio actualizado ${s(e.fetchedAt)}">
            Actualizado ${s(e.fetchedAt)}
          </span>
        </div>
      </div>
    `}function y(c,e){c.innerHTML=`
      <div class="btc-symbol" aria-hidden="true">₿</div>
      <div class="price-main">
        <p class="price-error">
          No se pudo cargar el precio de BTC/${e}.
          <a href="https://coingecko.com/en/coins/bitcoin" target="_blank" rel="noopener noreferrer">
            Ver en CoinGecko
          </a>.
        </p>
      </div>
    `}async function v(c){const e=c.dataset.currency??"USD",n=c.dataset.currencySymbol??"$",t=p(e);if(!(t&&(l(c,t,n,e),!m(t))))try{let r;try{r=await g(e)}catch{r=await w(e)}f(e,r),l(c,r,n,e)}catch{t||y(c,e)}}function h(){document.querySelectorAll("[data-currency][data-currency-symbol]").forEach(e=>{e.id.startsWith("price-widget-")&&v(e)})}h();document.addEventListener("astro:page-load",h);
