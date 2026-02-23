import"./hoisted.COZWcr4e.js";import"./hoisted.zV1UqMzv.js";import"./hoisted.CZoYlvhI.js";async function h(e,r,o){const a=`/api/forum.php?action=list_threads&category=${encodeURIComponent(r)}&limit=${o}`;try{const s=await fetch(a,{headers:{Accept:"application/json"},signal:AbortSignal.timeout(5e3)});if(!s.ok)throw new Error(`HTTP ${s.status}`);const i=await s.json(),n=Array.isArray(i)?i:i.threads??[];if(!n.length){e.setAttribute("aria-busy","false"),e.innerHTML='<p class="forum-error">No hay hilos recientes. <a href="/foro/">Sé el primero en publicar</a>.</p>';return}const u=`/foro/${r}/`;e.setAttribute("aria-busy","false"),e.innerHTML=n.slice(0,o).map(t=>{const m=t.url??`${u}${t.id}/`,f=t.replyCount!==void 0?`${t.replyCount} respuesta${t.replyCount!==1?"s":""}`:"",d=t.author?`por ${t.author}`:"",l=[f,d].filter(Boolean).join(" · ");return`
            <div class="forum-thread-item">
              <a href="${m}" class="forum-thread-title">${c(t.title)}</a>
              ${l?`<p class="forum-thread-meta">${c(l)}</p>`:""}
            </div>
          `}).join("")}catch{e.setAttribute("aria-busy","false"),e.innerHTML=`
        <p class="forum-error">
          No se pudieron cargar los hilos del foro.
          <a href="/foro/${encodeURIComponent(r)}/">Ver el foro directamente</a>.
        </p>
      `}}function c(e){return e.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}function p(){document.querySelectorAll("[data-country][data-max-items]").forEach(r=>{const o=r.dataset.country??"",a=parseInt(r.dataset.maxItems??"3",10);o&&h(r,o,a)})}p();document.addEventListener("astro:page-load",p);
