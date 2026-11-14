/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: frontend/js/rest.js
   TECNOLOGÍAS: JavaScript ES6, Fetch API
   DESCRIPCIÓN: Consumo asíncrono de la API y actualización segura de la interfaz HTML.
   ============================================================
*/
'use strict';
// El HTML permanece estático; toda comunicación con el inventario pasa por JSON.
const $ = id => document.getElementById(id);
const form = $('producto-form');
const filters = $('filtros');
const tbody = $('productos');
let csrf = '';
let busy = false;
let requestSequence = 0;
let timer;
// Conserva el formato del inventario original y muestra decimales solo si existen.
const pesos = n => '$' + new Intl.NumberFormat('es-CO', {minimumFractionDigits:0,maximumFractionDigits:2,useGrouping:'always'}).format(Number(n));
const number = n => new Intl.NumberFormat('es-CO').format(Number(n));
function mensaje(text, error = false) {
  const el = $('mensaje');
  el.hidden = !text;
  el.className = 'alert ' + (error ? 'alert-error' : 'alert-success');
  el.textContent = text;
  el.setAttribute('role', error ? 'alert' : 'status');
}
async function api(params = {}, method = 'GET', data) {
  const url = new URL('../backend/public/api.php', location.href);
  for (const [key,value] of Object.entries(params)) if (value !== '') url.searchParams.set(key,value);
  const options = {method,credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'};
  if (method !== 'GET') options.headers['X-CSRF-Token'] = csrf;
  if (data !== undefined) { options.headers['Content-Type']='application/json'; options.body=JSON.stringify(data); }
  const res = await fetch(url,options);
  let json;
  try { json = await res.json(); } catch { throw new Error('La API no devolvió JSON. Revisa PHP y Laragon.'); }
  if (!res.ok || !json.ok) throw new Error(json.error || `Error HTTP ${res.status}`);
  if (json.csrf) csrf=json.csrf;
  return json;
}
function element(tag, cls, text) {
  const e=document.createElement(tag); if(cls)e.className=cls; if(text!==undefined)e.textContent=text; return e;
}
function cell(row,label,cls) { const td=element('td',cls); td.dataset.label=label; row.append(td); return td; }
function button(label, cls, title, handler, disabled=false) {
  const b=element('button',cls,label); b.type='button'; b.title=title; b.setAttribute('aria-label',title); b.disabled=disabled;
  b.dataset.restAction='1'; b.dataset.limit=disabled?'1':'0'; b.addEventListener('click',handler); return b;
}
function setBusy(value) {
  busy=value;
  for (const el of form.querySelectorAll('input,select,button')) el.disabled=value || (!csrf && el.type!=='hidden');
  for (const el of tbody.querySelectorAll('button')) el.disabled=value || el.dataset.limit==='1';
  // Evita perder cambios de filtro durante una petición de escritura o edición.
  for (const el of filters.querySelectorAll('input,select,button')) el.disabled=value;
}
function opciones(select,values,placeholder) {
  const selected=select.value;
  select.replaceChildren(new Option(placeholder,''),...values.map(v=>new Option(v,v)));
  if(values.includes(selected))select.value=selected;
}
function pintar(productos) {
  tbody.replaceChildren();
  if (!productos.length) { const tr=element('tr'); const td=element('td','empty-state','No se encontraron productos.'); td.colSpan=5; tr.append(td); tbody.append(tr); }
  for (const p of productos) {
    const tr=element('tr'), stock=Number(p.stock);
    const product=cell(tr,'Producto','product-cell');
    const detail=element('div'); detail.append(element('strong','',p.nombre),element('small','',`${p.marca} · #${p.id}`));
    product.append(element('div','product-avatar',p.nombre.slice(0,1).toUpperCase()),detail);
    cell(tr,'Categoría').append(element('span','category-pill',p.categoria));
    cell(tr,'Precio','price-cell').textContent=pesos(p.precio);
    const exist=cell(tr,'Existencias');
    const status=stock===0?['out','● Agotado']:stock<=3?['low',`● Últimas ${stock} uds.`]:['good',`● ${stock} disp.`];
    exist.append(element('span',`stock-badge ${status[0]}`,status[1]));
    const adjustments=element('div','stock-actions');
    adjustments.append(button('−1','','Restar una unidad a '+p.nombre,()=>mutar({id:p.id},'PUT',{delta:-1}),stock<=0),button('+1','','Sumar una unidad a '+p.nombre,()=>mutar({id:p.id},'PUT',{delta:1}),stock>=1000000));
    exist.append(adjustments);
    const actions=element('div','row-actions');
    actions.append(button('✎','btn-icon edit','Editar '+p.nombre,()=>editar(p.id)),button('✕','btn-icon delete','Eliminar '+p.nombre,()=>{if(confirm('¿Eliminar este producto? Los movimientos históricos se conservarán.'))mutar({id:p.id},'DELETE');}));
    cell(tr,'Acciones').append(actions); tbody.append(tr);
  }
  $('contador').textContent=number(productos.length)+' resultados';
  $('total-resultados').textContent='Se muestran '+number(productos.length)+' registros según los filtros.';
  setBusy(busy);
}
async function cargarInventario() {
  const current=++requestSequence;
  const data=await api({q:$('buscar').value.trim(),categoria:$('filtro-categoria').value});
  if(current!==requestSequence)return;
  opciones($('categoria'),data.categorias,'Selecciona una categoría');
  opciones($('filtro-categoria'),data.categorias,'Todas las categorías');
  for(const [id,key] of [['kpi-productos','total_productos'],['kpi-unidades','total_unidades'],['kpi-valor','valor_inventario'],['kpi-bajo','stock_bajo']]) $(id).textContent=key==='valor_inventario'?pesos(data.kpis[key]):number(data.kpis[key]);
  pintar(data.productos);
}
function resetForm() {
  form.reset(); $('producto-id').value=''; $('stock').value='0'; $('form-titulo').textContent='Nuevo producto'; $('guardar').textContent='+ Registrar producto'; $('cancelar').hidden=true;
}
async function editar(id) {
  if(busy)return; setBusy(true);
  try {
    const data=await api({id}); const p=data.producto;
    $('producto-id').value=p.id;
    for (const field of ['nombre','marca','categoria','precio','stock']) $(field).value=p[field];
    $('form-titulo').textContent='Editar producto #'+p.id; $('guardar').textContent='Guardar cambios'; $('cancelar').hidden=false;
    $('formulario-producto').scrollIntoView({block:'start'}); mensaje('');
  } catch(e) {mensaje(e.message,true);} finally {setBusy(false);}
}
async function mutar(params,method,data,reset=false) {
  if(busy)return; setBusy(true); clearTimeout(timer); ++requestSequence;
  try {
    const result=await api(params,method,data);
    if(reset || (method==='DELETE' && $('producto-id').value===String(params.id)))resetForm();
    mensaje(result.mensaje);
    try{await cargarInventario();}catch(e){mensaje(result.mensaje+' No se pudo refrescar: '+e.message,true);}
  } catch(e) {mensaje(e.message,true);} finally {setBusy(false);}
}
form.addEventListener('submit',e=>{
  e.preventDefault(); if(busy || !form.reportValidity())return;
  const id=$('producto-id').value;
  const data=Object.fromEntries(['nombre','marca','categoria','precio','stock'].map(field=>[field,$(field).value]));
  mutar(id?{id}:{},id?'PUT':'POST',data,true);
});
$('cancelar').addEventListener('click',resetForm);
function buscar() {clearTimeout(timer); if(busy)return; cargarInventario().catch(e=>mensaje(e.message,true));}
filters.addEventListener('submit',e=>{e.preventDefault();buscar();});
$('buscar').addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(buscar,250);});
$('filtro-categoria').addEventListener('change',buscar);
$('limpiar').addEventListener('click',()=>{filters.reset();buscar();});
setBusy(true); cargarInventario().catch(e=>mensaje(e.message,true)).finally(()=>setBusy(false));
