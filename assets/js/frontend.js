(function($){
'use strict';

function labels(){ return window.kbl_front_labels || {}; }
function LBL(k,d){ return labels()[k] || d; }
function esc(v){ return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function mapsUrl(b){ return 'https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(b.latitude+','+b.longitude); }
function splitPhoneNumbers(v){
  return String(v==null?'':v).split(/[,;]|\s+[-–—]\s+/).map(function(n){return n.trim();}).filter(Boolean);
}
function phone(v){
  var numbers=splitPhoneNumbers(v);
  return numbers.length ? numbers.map(function(n){return '<a href="tel:'+esc(n.replace(/[^+0-9]/g,''))+'">'+esc(n)+'</a>';}).join(' <span class="kbl-phone-separator" aria-hidden="true"> / </span> ') : '—';
}
function primaryPhone(v){
  var numbers=splitPhoneNumbers(v);
  return numbers.length ? numbers[0].replace(/[^+0-9]/g,'') : '';
}
function badge(b){ return b.group_name ? '<span class="kbl-badge" style="background:'+esc(b.group_color||'#0a6b70')+';color:#fff;">'+esc(b.group_name)+'</span>' : ''; }
function normalize(v){ return String(v==null?'':v).trim().toLocaleLowerCase(); }
function compare(a,b,key,dir){
  var av=normalize(a[key]), bv=normalize(b[key]);
  if(av===bv){
    var nameA=normalize(a.name),nameB=normalize(b.name);
    if(nameA===nameB)return 0;
    return nameA.localeCompare(nameB,undefined,{numeric:true,sensitivity:'base'});
  }
  var r=av.localeCompare(bv,undefined,{numeric:true,sensitivity:'base'});
  return dir==='DESC'?-r:r;
}
function visibleRows(items,c){
  var q=normalize(c.searchValue), city=c.cityValue||'', group=c.groupValue||'';
  var out=items.filter(function(b){
    var hay=(String(b.name)+' '+String(b.city)+' '+String(b.address)+' '+String(b.phone)+' '+String(b.phone2||'')+' '+String(b.website||'')+' '+String(b.group_name)).toLocaleLowerCase();
    if(q && hay.indexOf(q)===-1)return false;
    if(city && String(b.city)!==String(city))return false;
    if(group && String(b.group_slug)!==String(group))return false;
    if(c.layout==='map' && c.hiddenGroups && c.hiddenGroups[b.group_slug||'__u__'])return false;
    return true;
  });
  if(c.sorting){
    var key=c.sortKey||'name',dir=c.sortOrder==='DESC'?'DESC':'ASC';
    out.sort(function(a,b){return compare(a,b,key,dir);});
  }
  return out;
}
function uniqueValues(items,key){
  var seen={},out=[];
  items.forEach(function(b){var v=String(b[key]||'');if(v&&!seen[v]){seen[v]=true;out.push(v);}});
  return out.sort(function(a,b){return a.localeCompare(b,undefined,{numeric:true,sensitivity:'base'});});
}
function formatCount(shown,total){
  var s=LBL('results_count','Showing %1$d of %2$d results.');
  return s.replace('%1$d',shown).replace('%2$d',total);
}
function hasActiveControls(c){ return !!(c.searchValue||c.cityValue||c.groupValue||(c.sortKey&&c.sortKey!=='name')||(c.sortOrder&&c.sortOrder!=='ASC')); }
var ICONS={"pin": "<path d=\"M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z\"/><circle cx=\"12\" cy=\"10\" r=\"2.6\"/>", "phone": "<path d=\"M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z\"/>", "eye": "<path d=\"M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/>", "search": "<circle cx=\"11\" cy=\"11\" r=\"7\"/><path d=\"m21 21-4.3-4.3\"/>", "city": "<path d=\"M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 10h.01M15 10h.01M9 13h.01M15 13h.01\"/>", "layers": "<path d=\"m12 2 10 5-10 5L2 7z\"/><path d=\"m2 12 10 5 10-5M2 17l10 5 10-5\"/>", "chev": "<path d=\"m9 6 6 6-6 6\"/>", "down": "<path d=\"m6 9 6 6 6-6\"/>", "store": "<path d=\"M3 9l1.5-5h15L21 9M3 9v11h18V9M3 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3M9 20v-5h6v5\"/>", "target": "<circle cx=\"12\" cy=\"12\" r=\"3\"/><circle cx=\"12\" cy=\"12\" r=\"8\"/><path d=\"M12 2v3M12 19v3M2 12h3M19 12h3\"/>", "globe": "<circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20\"/>", "x": "<path d=\"M18 6 6 18M6 6l12 12\"/>"};
function ico(n,cls){return '<svg class="kbl2-ico'+(cls?' '+cls:'')+'" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'+(ICONS[n]||'')+'</svg>';}

function plainPhones(v){ var n=splitPhoneNumbers(v); return n.length?'<span class="kbl2-phone" dir="ltr">'+n.map(function(x){return '<a href="tel:'+esc(x.replace(/[^+0-9]/g,''))+'">'+esc(x)+'</a>';}).join('<span class="kbl2-sep"> / </span>')+'</span>':'—'; }
function gbadge(b){ return b.group_name?'<span class="kbl2-badge" style="--kbl2-c:'+esc(b.group_color||'#0a6b70')+'">'+esc(b.group_name)+'</span>':''; }
function mapBtn(b,c){ return c.show_directions?'<a class="kbl2-btn kbl2-btn-primary" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+ico('pin')+'<span>'+esc(LBL('show_on_map','View on map'))+'</span></a>':''; }
function detBtn(b){ return '<button type="button" class="kbl2-btn kbl2-btn-ghost kbl2-open-details" data-id="'+esc(b.id)+'"><span>'+esc(LBL('details','Details'))+'</span>'+ico('chev','kbl2-flip')+'</button>'; }
function toolbar(c,items,total,shown){
  var hasFilters=c.city_filter||c.group_filter, hasSearch=c.search||c.layout==='map-table';
  if(!hasFilters&&!hasSearch)return '';
  var h='<div class="kbl2-toolbar">';
  if(hasSearch)h+='<div class="kbl2-field kbl2-field-search">'+ico('search')+'<input class="kbl2-search" type="search" value="'+esc(c.searchValue||'')+'" placeholder="'+esc(LBL('search_branches','Search branches...'))+'" aria-label="'+esc(LBL('search_label','Search'))+'"></div>';
  if(c.city_filter){
    h+='<label class="kbl2-field kbl2-field-select">'+ico('city')+'<select class="kbl2-city" aria-label="'+esc(LBL('filter_city','City'))+'"><option value="">'+esc(LBL('all_cities','All Cities'))+'</option>';
    uniqueValues(items,'city').forEach(function(v){h+='<option value="'+esc(v)+'"'+(String(c.cityValue||'')===v?' selected':'')+'>'+esc(v)+'</option>';});
    h+='</select>'+ico('down','kbl2-caret')+'</label>';
  }
  if(c.group_filter){
    var groups=[];items.forEach(function(b){if(b.group_slug&&!groups.some(function(g){return g.slug===b.group_slug;}))groups.push({slug:b.group_slug,name:b.group_name||b.group_slug});});
    groups.sort(function(a,b){return a.name.localeCompare(b.name,undefined,{numeric:true,sensitivity:'base'});});
    h+='<label class="kbl2-field kbl2-field-select">'+ico('layers')+'<select class="kbl2-group" aria-label="'+esc(LBL('filter_group','Group'))+'"><option value="">'+esc(LBL('all_groups','All Groups'))+'</option>';
    groups.forEach(function(g){h+='<option value="'+esc(g.slug)+'"'+(String(c.groupValue||'')===g.slug?' selected':'')+'>'+esc(g.name)+'</option>';});
    h+='</select>'+ico('down','kbl2-caret')+'</label>';
  }
  if(hasSearch)h+='<button type="button" class="kbl2-btn kbl2-btn-primary kbl2-submit">'+ico('search')+'<span>'+esc(LBL('search_btn','Search'))+'</span></button>';
  if(hasActiveControls(c))h+='<button type="button" class="kbl2-btn kbl2-btn-ghost kbl2-reset"><span>'+esc(LBL('reset_filters','Reset'))+'</span></button>';
  h+='</div><div class="kbl2-count" aria-live="polite">'+esc(formatCount(shown,total))+'</div>';
  return h;
}
function info(b,c){
  var h='<div class="kbl-popup"><strong>'+esc(b.name)+'</strong>';
  if(b.group_name)h+=' '+gbadge(b);
  if(c.show_city)h+='<div>'+ico('city')+' '+esc(b.city||'—')+'</div>';
  if(c.show_address)h+='<div>'+ico('pin')+' '+esc(b.address||'—')+'</div>';
  if(c.show_phone)h+='<div>'+ico('phone')+' '+plainPhones(b.phone)+'</div>';
  h+='<div class="kbl2-actions">';
  if(c.show_call&&b.phone)h+='<a class="kbl2-btn kbl2-btn-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+ico('phone')+'<span>'+esc(LBL('call','Call'))+'</span></a>';
  if(c.show_directions)h+='<a class="kbl2-btn kbl2-btn-primary" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+ico('pin')+'<span>'+esc(LBL('directions','Directions'))+'</span></a>';
  return h+'</div></div>';
}
function iconBtns(b,c){
  var h='<span class="kbl2-icons"><button type="button" class="kbl2-iconbtn kbl2-open-details" data-id="'+esc(b.id)+'" aria-label="'+esc(LBL('details','Details'))+'" title="'+esc(LBL('details','Details'))+'">'+ico('eye')+'</button>';
  if(c.show_directions)h+='<a class="kbl2-iconbtn" target="_blank" rel="noopener" href="'+mapsUrl(b)+'" aria-label="'+esc(LBL('show_on_map','View on map'))+'" title="'+esc(LBL('show_on_map','View on map'))+'">'+ico('pin')+'</a>';
  return h+'</span>';
}
function table(items,c,total,shown){
  var off=c._offset||0;
  var h='<div class="kbl2-table-wrap"><table class="kbl2-table"><thead><tr><th scope="col" class="kbl2-num">#</th>';
  function th(label,key,enabled){
    var active=c.sorting&&c.sortKey===key, icon=active?(c.sortOrder==='DESC'?'↓':'↑'):'↕';
    if(c.sorting&&enabled)return '<th scope="col"><button type="button" class="kbl2-sort" data-sort-key="'+key+'" title="'+esc(LBL('sort_by','Sort by')+' '+label)+'"><span>'+esc(label)+'</span><span class="kbl2-sorticon" aria-hidden="true">'+icon+'</span></button></th>';
    return '<th scope="col">'+esc(label)+'</th>';
  }
  if(c.show_name)h+=th(LBL('branch','Branch'),'name',true);
  if(c.show_city)h+=th(LBL('city','City'),'city',true);
  h+=th(LBL('group','Group'),'group_name',true);
  if(c.show_address)h+=th(LBL('address','Address'),'address',false);
  if(c.show_phone)h+=th(LBL('phone','Phone'),'phone',false);
  h+='<th scope="col" class="kbl2-actions-col">'+esc(LBL('actions','Actions'))+'</th></tr></thead><tbody>';
  if(!items.length)return h+'<tr><td class="kbl2-empty" colspan="10">'+esc(LBL('no_results','No branches found.'))+'</td></tr></tbody></table></div>';
  items.forEach(function(b,i){
    h+='<tr class="kbl-row" data-id="'+esc(b.id)+'"><td class="kbl2-num" data-label="#">'+(off+i+1)+'</td>';
    if(c.show_name)h+='<td data-label="'+esc(LBL('branch','Branch'))+'"><strong>'+esc(b.name)+'</strong></td>';
    if(c.show_city)h+='<td data-label="'+esc(LBL('city','City'))+'">'+esc(b.city||'—')+'</td>';
    h+='<td data-label="'+esc(LBL('group','Group'))+'">'+gbadge(b)+'</td>';
    if(c.show_address)h+='<td data-label="'+esc(LBL('address','Address'))+'"><span class="kbl2-with-ico">'+ico('pin')+'<span>'+esc(b.address||'—')+'</span></span></td>';
    if(c.show_phone)h+='<td data-label="'+esc(LBL('phone','Phone'))+'">'+plainPhones(b.phone)+'</td>';
    h+='<td class="kbl2-actions-col" data-label="">'+iconBtns(b,c)+'</td></tr>';
  });
  return h+'</tbody></table></div>';
}
function card(b,c){
  var h='<article class="kbl2-card kbl-row" data-id="'+esc(b.id)+'" style="--kbl2-c:'+esc(b.group_color||'#315bea')+'"><div class="kbl2-card-top">';
  if(c.show_name)h+='<h3 class="kbl2-card-title">'+esc(b.name)+'</h3>';
  if(c.show_group&&b.group_name)h+=gbadge(b);
  h+='</div><ul class="kbl2-meta">';
  if(c.show_city)h+='<li>'+ico('city')+'<span>'+esc(b.city||'—')+'</span></li>';
  if(c.show_address)h+='<li>'+ico('pin')+'<span>'+esc(b.address||'—')+'</span></li>';
  if(c.show_phone)h+='<li>'+ico('phone')+plainPhones(b.phone)+'</li>';
  return h+'</ul><div class="kbl2-card-actions">'+mapBtn(b,c)+detBtn(b)+'</div></article>';
}
function cards(items,c){
  if(!items.length)return '<div class="kbl-empty kbl2-emptybox">'+esc(LBL('no_results','No branches found.'))+'</div>';
  if(!c.group_cards){var p='<div class="kbl2-cards">';items.forEach(function(b){p+=card(b,c);});return p+'</div>';}
  var groups={},order=[];
  items.forEach(function(b){var k=b.group_slug||'__u__';if(!groups[k]){groups[k]={name:b.group_name||LBL('ungrouped','Other branches'),color:b.group_color||'#0a6b70',items:[]};order.push(k);}groups[k].items.push(b);});
  var h='<div class="kbl2-card-groups">';
  order.forEach(function(k){var g=groups[k];h+='<section class="kbl2-card-section"><h2 class="kbl2-section-title" style="--kbl2-c:'+esc(g.color)+'">'+esc(g.name)+'</h2><div class="kbl2-cards">';g.items.forEach(function(b){h+=card(b,c);});h+='</div></section>';});
  return h+'</div>';
}
function accordion(items,c){
  if(!items.length)return '<div class="kbl-empty kbl2-emptybox">'+esc(LBL('no_results','No branches found.'))+'</div>';
  var groups={},order=[];
  items.forEach(function(b){var k=b.group_slug||'__u__';if(!groups[k]){groups[k]={name:b.group_name||LBL('group','Group'),color:b.group_color||'#315bea',items:[]};order.push(k);}groups[k].items.push(b);});
  var h='<div class="kbl2-acc-list">';
  order.forEach(function(k,idx){
    var g=groups[k],open=idx===0;
    h+='<section class="kbl2-acc'+(open?' is-open':'')+'" style="--kbl2-c:'+esc(g.color)+'"><button type="button" class="kbl2-acc-head" aria-expanded="'+(open?'true':'false')+'">'+ico('down','kbl2-acc-chev')+'<span class="kbl2-acc-title"><span class="kbl2-acc-ico">'+ico('store')+'</span><span>'+esc(g.name)+'</span><small>('+g.items.length+')</small></span></button>';
    h+='<div class="kbl2-acc-body"'+(open?'':' hidden')+'><div class="kbl2-table-wrap"><table class="kbl2-table kbl2-table-plain"><thead><tr>';
    if(c.show_name)h+='<th>'+esc(LBL('branch','Branch'))+'</th>';
    if(c.show_city)h+='<th>'+esc(LBL('city','City'))+'</th>';
    if(c.show_address)h+='<th>'+esc(LBL('address','Address'))+'</th>';
    if(c.show_phone)h+='<th>'+esc(LBL('phone','Phone'))+'</th>';
    h+='<th class="kbl2-actions-col"></th></tr></thead><tbody>';
    g.items.forEach(function(b){
      h+='<tr class="kbl-row" data-id="'+esc(b.id)+'">';
      if(c.show_name)h+='<td data-label="'+esc(LBL('branch','Branch'))+'"><strong>'+esc(b.name)+'</strong></td>';
      if(c.show_city)h+='<td data-label="'+esc(LBL('city','City'))+'">'+esc(b.city||'—')+'</td>';
      if(c.show_address)h+='<td data-label="'+esc(LBL('address','Address'))+'"><span class="kbl2-with-ico">'+ico('pin')+'<span>'+esc(b.address||'—')+'</span></span></td>';
      if(c.show_phone)h+='<td data-label="'+esc(LBL('phone','Phone'))+'"><span class="kbl2-with-ico">'+ico('phone')+plainPhones(b.phone)+'</span></td>';
      h+='<td class="kbl2-actions-col">'+iconBtns(b,c)+'</td></tr>';
    });
    h+='</tbody></table></div></div></section>';
  });
  return h+'</div>';
}
function list(items,c){
  if(!items.length)return '<div class="kbl-empty kbl2-emptybox">'+esc(LBL('no_results','No branches found.'))+'</div>';
  var h='<div class="kbl2-list">';
  items.forEach(function(b){
    h+='<article class="kbl2-list-item kbl-row" data-id="'+esc(b.id)+'" style="--kbl2-c:'+esc(b.group_color||'#f05c41')+'"><div class="kbl2-list-main">';
    if(c.show_name)h+='<h3 class="kbl2-list-title">'+esc(b.name)+'</h3>';
    if(b.group_name)h+=gbadge(b);
    h+='</div><div class="kbl2-list-cols">';
    if(c.show_city)h+='<span class="kbl2-with-ico">'+ico('city')+'<span>'+esc(b.city||'—')+'</span></span>';
    if(c.show_address)h+='<span class="kbl2-with-ico">'+ico('pin')+'<span>'+esc(b.address||'—')+'</span></span>';
    if(c.show_phone)h+='<span class="kbl2-with-ico">'+ico('phone')+plainPhones(b.phone)+'</span>';
    h+='</div><button type="button" class="kbl2-iconbtn kbl2-open-details kbl2-list-go" data-id="'+esc(b.id)+'" aria-label="'+esc(LBL('details','Details'))+'">'+ico('chev','kbl2-flip')+'</button></article>';
  });
  return h+'</div>';
}
function detailsModal(b,c){
  function f(l,v,i){return v?'<div class="kbl2-dl-row">'+ico(i)+'<div><span>'+esc(l)+'</span><strong>'+v+'</strong></div></div>':'';}
  var h='<div class="kbl2-modal" role="dialog" aria-modal="true" aria-label="'+esc(b.name)+'"><div class="kbl2-modal-box" style="--kbl2-c:'+esc(b.group_color||'#315bea')+'"><button type="button" class="kbl2-iconbtn kbl2-modal-close" aria-label="'+esc(LBL('close','Close'))+'">'+ico('x')+'</button><h3>'+esc(b.name)+'</h3>'+gbadge(b);
  h+='<div class="kbl2-dl">'+f(LBL('city','City'),esc(b.city||''),'city')+f(LBL('address','Address'),esc(b.address||''),'pin')+f(LBL('phone','Phone'),b.phone?plainPhones(b.phone):'','phone')+f(LBL('phone2','Secondary phone'),b.phone2?plainPhones(b.phone2):'','phone')+f(LBL('website','Website'),b.website?'<a target="_blank" rel="noopener noreferrer" href="'+esc(b.website)+'">'+esc(LBL('visit_website','Visit website'))+'</a>':'','globe')+'</div><div class="kbl2-actions">';
  if(b.phone)h+='<a class="kbl2-btn kbl2-btn-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+ico('phone')+'<span>'+esc(LBL('call','Call'))+'</span></a>';
  if(Number.isFinite(Number.parseFloat(b.latitude))&&Number.isFinite(Number.parseFloat(b.longitude)))h+='<a class="kbl2-btn kbl2-btn-primary" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+ico('pin')+'<span>'+esc(LBL('directions','Directions'))+'</span></a>';
  return h+'</div></div></div>';
}
function paginate($el,page,total,c){
  if(!c.pagination||total<=1){$el.empty();return;}
  var h='<span class="kbl-pagination-label">'+esc(LBL('page','Page'))+' '+page+' / '+total+'</span>';
  for(var i=1;i<=total;i++)h+='<button type="button" class="kbl-page '+(i===page?'active':'')+'" data-page="'+i+'" aria-label="'+esc(LBL('go_to_page','Go to page')+' '+i)+'">'+i+'</button>';
  $el.html(h);
}

function MapView(el,c,app){
  this.el=el;this.c=c;this.app=app;this.center=[27,17];this.zoom=c.zoom||6;this.markers={};this.popup=null;this.tile=256;
  this.el.classList.add('kbl-map');
  this.el.innerHTML='<div class="kbl-map-viewport"><div class="kbl-map-tiles"></div><div class="kbl-map-markers"></div><div class="kbl-map-popup-layer"></div></div><div class="kbl-map-controls"><button type="button" class="kbl-zi" aria-label="'+esc(LBL('zoom_in','Zoom in'))+'" title="'+esc(LBL('zoom_in','Zoom in'))+'">+</button><button type="button" class="kbl-zo" aria-label="'+esc(LBL('zoom_out','Zoom out'))+'" title="'+esc(LBL('zoom_out','Zoom out'))+'">−</button><button type="button" class="kbl-loc" aria-label="'+esc(LBL('my_location','My location'))+'" title="'+esc(LBL('my_location','My location'))+'">'+ico('target')+'</button></div><div class="kbl2-legend" hidden></div><div class="kbl-map-attribution">© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors</div>';
  this.viewport=this.el.querySelector('.kbl-map-viewport');this.tiles=this.el.querySelector('.kbl-map-tiles');this.layer=this.el.querySelector('.kbl-map-markers');this.popupLayer=this.el.querySelector('.kbl-map-popup-layer');
  this.dragging=false;this.dragStart=null;this.touchStart=null;this.touchActive=false;
  this.touchHint=document.createElement('div');this.touchHint.className='kbl-map-touch-hint';this.touchHint.setAttribute('aria-hidden','true');this.touchHint.textContent=LBL('map_touch_hint','Use two fingers to move the map');this.el.appendChild(this.touchHint);
  var self=this;this.el.querySelector('.kbl-zi').onclick=function(){self.setZoom(self.zoom+1)};this.el.querySelector('.kbl-zo').onclick=function(){self.setZoom(self.zoom-1)};this.el.querySelector('.kbl-loc').onclick=function(){if(!navigator.geolocation)return;navigator.geolocation.getCurrentPosition(function(p){self.center=[p.coords.latitude,p.coords.longitude];self.setZoom(Math.max(self.zoom,10));});};this.legend=this.el.querySelector('.kbl2-legend');this.onResize=function(){self.draw();};window.addEventListener('resize',this.onResize);
  this.bindPan();
}
MapView.prototype.bindPan=function(){
  var self=this;
  this.viewport.addEventListener('mousedown',function(e){
    if(e.button!==0)return;
    self.dragging=true;self.dragStart={x:e.clientX,y:e.clientY,center:self.project(self.center[0],self.center[1],self.zoom)};
    self.viewport.classList.add('is-dragging');
    e.preventDefault();
  });
  this.onMouseMove=function(e){
    if(!self.dragging)return;
    var dx=e.clientX-self.dragStart.x,dy=e.clientY-self.dragStart.y,cp=self.dragStart.center;
    self.center=self.unproject(cp.x-dx,cp.y-dy,self.zoom);self.draw();
  };
  this.onMouseUp=function(){
    if(!self.dragging)return;
    self.dragging=false;self.viewport.classList.remove('is-dragging');
  };
  window.addEventListener('mousemove',this.onMouseMove);
  window.addEventListener('mouseup',this.onMouseUp);
  this.viewport.addEventListener('mouseleave',function(){
    if(self.dragging){self.dragging=false;self.viewport.classList.remove('is-dragging');}
  });
  this.viewport.addEventListener('touchstart',function(e){
    if(e.touches.length===2){
      var m=self.touchMidpoint(e.touches);
      self.touchActive=true;self.touchStart={x:m.x,y:m.y,center:self.project(self.center[0],self.center[1],self.zoom)};
    } else if(e.touches.length===1){
      self.touchActive=false;self.touchStart=null;
    }
  },{passive:true});
  this.viewport.addEventListener('touchmove',function(e){
    if(e.touches.length!==2||!self.touchActive||!self.touchStart)return;
    var m=self.touchMidpoint(e.touches),dx=m.x-self.touchStart.x,dy=m.y-self.touchStart.y,cp=self.touchStart.center;
    e.preventDefault();
    self.center=self.unproject(cp.x-dx,cp.y-dy,self.zoom);self.draw();
    self.touchHint.classList.add('is-hidden');
  },{passive:false});
  this.viewport.addEventListener('touchend',function(e){
    if(e.touches.length<2){self.touchActive=false;self.touchStart=null;}
  },{passive:true});
  this.viewport.addEventListener('touchcancel',function(){self.touchActive=false;self.touchStart=null;},{passive:true});
};
MapView.prototype.touchMidpoint=function(touches){return{x:(touches[0].clientX+touches[1].clientX)/2,y:(touches[0].clientY+touches[1].clientY)/2};};
MapView.prototype.project=function(lat,lng,z){var s=this.tile*Math.pow(2,z),x=(lng+180)/360*s,r=Math.max(-85.05112878,Math.min(85.05112878,lat))*Math.PI/180,y=(1-Math.log(Math.tan(r)+1/Math.cos(r))/Math.PI)/2*s;return{x:x,y:y};};
MapView.prototype.unproject=function(x,y,z){var s=this.tile*Math.pow(2,z),lng=x/s*360-180,n=Math.PI-2*Math.PI*y/s,lat=180/Math.PI*Math.atan(.5*(Math.exp(n)-Math.exp(-n)));return[lat,lng];};
MapView.prototype.setZoom=function(z){this.zoom=Math.max(1,Math.min(18,z));this.draw();};
MapView.prototype.fit=function(points){if(!points.length){this.center=[27,17];this.zoom=6;this.draw();return;}var minLa=90,maxLa=-90,minLn=180,maxLn=-180;points.forEach(function(p){minLa=Math.min(minLa,p[0]);maxLa=Math.max(maxLa,p[0]);minLn=Math.min(minLn,p[1]);maxLn=Math.max(maxLn,p[1]);});this.center=[(minLa+maxLa)/2,(minLn+maxLn)/2];if(points.length===1)this.zoom=Math.max(6,this.c.zoom||6);else this.zoom=Math.min(this.c.zoom||6,10);this.draw();};
MapView.prototype.add=function(b){var self=this,el=document.createElement('button');el.type='button';el.className='kbl-marker';el.title=b.name;el.setAttribute('aria-label',b.name);el.style.setProperty('--kbl2-c',b.group_color||'#e33434');el.innerHTML='<svg viewBox="0 0 32 42" width="32" height="42" aria-hidden="true"><path d="M16 41S3 26.5 3 15.5a13 13 0 0 1 26 0C29 26.5 16 41 16 41z" fill="currentColor" stroke="#fff" stroke-width="2"/><circle cx="16" cy="15.5" r="8.5" fill="#fff"/><g transform="translate(10 9.5) scale(.5)" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9M3 9v11h18V9M9 20v-5h6v5"/></g></svg>';el.onclick=function(e){e.stopPropagation();self.app.select(b.id,true);};this.layer.appendChild(el);this.markers[String(b.id)]={b:b,el:el};};
MapView.prototype.clear=function(){this.layer.innerHTML='';this.popupLayer.innerHTML='';this.markers={};this.popup=null;};
MapView.prototype.destroy=function(){window.removeEventListener('resize',this.onResize);window.removeEventListener('mousemove',this.onMouseMove);window.removeEventListener('mouseup',this.onMouseUp);};
MapView.prototype.open=function(b){var m=this.markers[String(b.id)];if(!m)return;this.close();var p=document.createElement('div');p.className='kbl-map-popup';p.innerHTML=info(b,this.c)+'<button class="kbl-close" type="button" aria-label="'+esc(LBL('close','Close'))+'" title="'+esc(LBL('close','Close'))+'">×</button>';p.querySelector('.kbl-close').onclick=this.close.bind(this);this.popupLayer.appendChild(p);this.popup={b:b,el:p};this.draw();};
MapView.prototype.close=function(){if(this.popup&&this.popup.el)this.popup.el.remove();this.popup=null;};
MapView.prototype.draw=function(){if(!this.el.clientWidth||!this.el.clientHeight)return;var w=this.el.clientWidth,h=this.el.clientHeight,cp=this.project(this.center[0],this.center[1],this.zoom),startX=Math.floor((cp.x-w/2)/256)-1,endX=Math.floor((cp.x+w/2)/256)+1,startY=Math.floor((cp.y-h/2)/256)-1,endY=Math.floor((cp.y+h/2)/256)+1,n=Math.pow(2,this.zoom);this.tiles.innerHTML='';for(var y=startY;y<=endY;y++){if(y<0||y>=n)continue;for(var x=startX;x<=endX;x++){var wx=((x%n)+n)%n,img=document.createElement('img');img.src='https://tile.openstreetmap.org/'+this.zoom+'/'+wx+'/'+y+'.png';img.alt='';img.className='kbl-tile';img.style.left=(x*256-cp.x+w/2)+'px';img.style.top=(y*256-cp.y+h/2)+'px';this.tiles.appendChild(img);}}var self=this;Object.keys(this.markers).forEach(function(id){var m=self.markers[id],p=self.project(m.b.latitude,m.b.longitude,self.zoom);m.el.style.left=(p.x-cp.x+w/2-16)+'px';m.el.style.top=(p.y-cp.y+h/2-42)+'px';});if(this.popup){var p=this.project(this.popup.b.latitude,this.popup.b.longitude,this.zoom);this.popup.el.style.left=(p.x-cp.x+w/2-150)+'px';this.popup.el.style.top=(p.y-cp.y+h/2-205)+'px';}};

function App($root){
  this.$root=$root;this.root=$root[0];this.c=JSON.parse($root.attr('data-config')||'{}');
  this.c.sortKey=this.c.sortKey||'name';this.c.sortOrder=this.c.sortOrder==='DESC'?'DESC':'ASC';
  this.all=Array.isArray(this.c.items)?this.c.items:[];this.page=1;this.map=null;this.loadingMore=false;this.bind();this.render();
}
App.prototype.bind=function(){
  var self=this;
  this.$root.on('input','.kbl2-search',function(){
    var input=this,start=typeof input.selectionStart==='number'?input.selectionStart:null,end=typeof input.selectionEnd==='number'?input.selectionEnd:null;
    self.c.searchValue=$(input).val()||'';self.page=1;self.render();
    var next=self.$root.find('.kbl2-search').get(0);
    if(next){next.focus({preventScroll:true});if(start!==null&&end!==null){var len=String(next.value||'').length;try{next.setSelectionRange(Math.min(start,len),Math.min(end,len));}catch(err){}}}
  });
  this.$root.on('click','.kbl2-submit',function(){self.c.searchValue=self.$root.find('.kbl2-search').val()||'';self.page=1;self.render();});
  this.$root.on('keydown','.kbl2-search',function(e){if(e.key==='Enter'){e.preventDefault();self.$root.find('.kbl2-submit').trigger('click');}});
  this.$root.on('change','.kbl2-city',function(){self.c.cityValue=$(this).val()||'';self.page=1;self.render();});
  this.$root.on('change','.kbl2-group',function(){self.c.groupValue=$(this).val()||'';self.page=1;self.render();});
  this.$root.on('click','.kbl2-sort',function(){var k=$(this).data('sort-key');if(self.c.sortKey===k)self.c.sortOrder=self.c.sortOrder==='DESC'?'ASC':'DESC';else{self.c.sortKey=k;self.c.sortOrder='ASC';}self.page=1;self.render();});
  this.$root.on('click','.kbl2-reset',function(){self.c.searchValue='';self.c.cityValue='';self.c.groupValue='';self.c.sortKey='name';self.c.sortOrder='ASC';self.c.hiddenGroups={};self.page=1;self.render();});
  this.$root.on('click','.kbl2-open-details',function(e){e.stopPropagation();var id=$(this).data('id'),b=self.all.find(function(x){return String(x.id)===String(id);});if(!b)return;self.$root.find('.kbl2-modal').remove();self.lastFocus=this;self.$root.append(detailsModal(b,self.c));self.$root.find('.kbl2-modal-close').trigger('focus');});
  this.$root.on('click','.kbl2-modal-close',function(){self.$root.find('.kbl2-modal').remove();if(self.lastFocus)$(self.lastFocus).trigger('focus');});
  this.$root.on('click','.kbl2-modal',function(e){if(e.target===this)self.$root.find('.kbl2-modal').remove();});
  this.$root.on('keydown',function(e){if(e.key==='Escape')self.$root.find('.kbl2-modal').remove();});
  this.$root.on('change','.kbl2-legend input',function(){self.c.hiddenGroups=self.c.hiddenGroups||{};self.c.hiddenGroups[$(this).val()]=!this.checked;self.render();});
  this.$root.on('click','.kbl-page',function(){self.page=Number.parseInt($(this).data('page'),10)||1;self.render();});
  $(window).on('scroll.kblInfinite'+(this.root.id||Math.random().toString(36).slice(2)),function(){
    if(self.c.display_mode!=='infinite_scroll'||!self.c.pagination||self.loadingMore)return;
    var items=visibleRows(self.all,self.c),pages=Math.max(1,Math.ceil(items.length/Math.max(1,self.c.per_page||15)));
    if(self.page>=pages)return;
    var rect=self.root.getBoundingClientRect();
    if(rect.bottom < window.innerHeight+240){self.loadingMore=true;self.page++;self.render();self.loadingMore=false;}
  });
  this.$root.on('click','.kbl-row[data-id]',function(e){if($(e.target).closest('a,button').length)return;self.select($(this).data('id'),false);});
  this.$root.on('click','.kbl2-acc-head',function(){var x=$(this).closest('.kbl2-acc'),open=!x.hasClass('is-open');x.toggleClass('is-open',open);$(this).attr('aria-expanded',open?'true':'false');var body=x.find('.kbl2-acc-body').first();if(open)body.prop('hidden',false).hide().slideDown(160);else body.slideUp(160,function(){body.prop('hidden',true);});});
};
App.prototype.select=function(id,fromMarker){
  var self=this,b=this.all.find(function(x){return String(x.id)===String(id);});
  if(!b)return;
  if(fromMarker){
    var current=visibleRows(this.all,this.c),idx=current.findIndex(function(x){return String(x.id)===String(id);});
    if(this.c.pagination&&idx>=0){this.page=Math.floor(idx/(this.c.per_page||15))+1;}
    this.render();
  }
  this.$root.find('.kbl-row').removeClass('active');
  this.$root.find('.kbl-row[data-id="'+id+'"]').addClass('active');
  if(this.map){
    Object.keys(this.map.markers).forEach(function(k){self.map.markers[k].el.classList.toggle('active',String(k)===String(id));});
    if(Number.isFinite(Number.parseFloat(b.latitude))&&Number.isFinite(Number.parseFloat(b.longitude))){self.map.center=[b.latitude,b.longitude];self.map.open(b);self.map.draw();}
    if(!fromMarker){var r=this.$root.find('.kbl-row[data-id="'+id+'"]').get(0);if(r)r.scrollIntoView({behavior:'smooth',block:'nearest'});}
  }
};
App.prototype.render=function(){
  var c=this.c,items=visibleRows(this.all,c),per=Math.max(1,c.per_page||15),totalPages=c.pagination?Math.max(1,Math.ceil(items.length/per)):1;
  if(this.page>totalPages)this.page=totalPages;
  var infinite=c.display_mode==='infinite_scroll'&&c.pagination;
  var shown=c.pagination?(infinite?items.slice(0,this.page*per):items.slice((this.page-1)*per,this.page*per)):items;
  var layout=c.layout||'table';
  c._offset=infinite?0:(c.pagination?(this.page-1)*per:0);
  this.$root.find('.kbl-toolbar').html(toolbar(c,this.all,items.length,shown.length));
  var html=layout==='table'?table(shown,c,items.length,shown.length):layout==='cards'?cards(shown,c):layout==='accordion'?accordion(shown,c):layout==='list'?list(shown,c):'';
  if(layout!=='map'&&layout!=='map-table'&&this.map){this.map.destroy();this.map=null;}
  if(layout==='map')this.$root.find('.kbl-results').html(table(shown,c,items.length,shown.length));
  else if(layout==='map-table')this.$root.find('.kbl-results').html(table(shown,c,items.length,shown.length));
  else this.$root.find('.kbl-results').html(html);
  if(infinite){this.$root.find('.kbl-pagination').empty();if(this.page<totalPages)this.$root.find('.kbl-pagination').html('<span class="kbl-pagination-label">'+esc(LBL('loading_more','Scroll to load more'))+'</span>');}
  else paginate(this.$root.find('.kbl-pagination'),this.page,totalPages,c);
  if(layout==='map'||layout==='map-table')this.renderMap(items);
};
App.prototype.renderMap=function(items){
  var self=this;
  var mapEl=this.$root.find('.kbl-map').get(0);
  if(!mapEl)return;
  if(!this.map||this.map.el!==mapEl){if(this.map)this.map.destroy();this.map=new MapView(mapEl,this.c,this);}
  this.map.c=this.c;
  this.map.clear();var pts=[];
  items.forEach(function(b){var lat=Number.parseFloat(b.latitude),lng=Number.parseFloat(b.longitude);if(!Number.isFinite(lat)||!Number.isFinite(lng)||lat===0&&lng===0)return;self.map.add(b);pts.push([lat,lng]);});
  this.map.fit(pts);
  if(this.map.legend){
    if((this.c.layout||'table')==='map'){var gs=[],seen={};this.all.forEach(function(b){var k=b.group_slug||'__u__';if(!seen[k]){seen[k]=1;gs.push({k:k,n:b.group_name||LBL('group','Group'),c:b.group_color||'#0a6b70'});}});
      var lh='<strong>'+esc(LBL('legend','Groups'))+'</strong>';gs.forEach(function(g){lh+='<label><input type="checkbox" value="'+esc(g.k)+'"'+((self.c.hiddenGroups&&self.c.hiddenGroups[g.k])?'':' checked')+'><span style="--kbl2-c:'+esc(g.c)+'">'+ico('pin','kbl2-colored')+'</span><span>'+esc(g.n)+'</span></label>';});
      this.map.legend.innerHTML=lh;this.map.legend.hidden=gs.length<2;}
    else this.map.legend.hidden=true;}
  var notice=this.$root.find('.kbl-map-empty');
  if(!pts.length){if(!notice.length)this.$root.find('.kbl-map').append('<div class="kbl-map-empty">'+esc(LBL('no_coordinates','No valid coordinates are available for these branches.'))+'</div>');}
  else notice.remove();
};
window.KBLFrontend={
  updatePreview:function(element,config,items){
    var root=$(element),layout=config.layout||'table',height=Math.max(300,Math.min(1200,Number.parseInt(config.height,10)||520));
    root.attr('dir',document.documentElement.dir==='rtl'?'rtl':'ltr')
      .attr('class','kbl-locator-wrap kbl-v2 kbl-layout-'+layout)
      .css({'--kbl-call-color':config.call_color,'--kbl-directions-color':config.directions_color,'--kbl-website-color':config.website_color,'--kbl-card-columns':config.columns});
    var shellLayout=root.data('kbl-preview-layout');
    if(shellLayout!==layout){
      var content=layout==='map-table'
        ? '<div class="kbl-map-table-shell"><div class="kbl-map-table-map"><div class="kbl-map" style="height:'+height+'px"></div></div><div class="kbl-map-table-list"><div class="kbl-results"></div></div></div>'
        : layout==='map'
          ? '<div class="kbl-map" style="height:'+height+'px"></div><div class="kbl-results"></div>'
          : '<div class="kbl-results"></div>';
      root.html('<div class="kbl-toolbar"></div>'+content+'<div class="kbl-pagination"></div>');
      root.data('kbl-preview-layout',layout);
    }else{
      root.find('.kbl-map').css('height',height+'px');
    }
    config.items=items;
    root.attr('data-config',JSON.stringify(config));
    var app=root.data('kblApp');
    if(!app){app=new App(root);root.data('kblApp',app);}
    else{app.c=config;app.all=items;app.page=1;app.render();}
  }
};
$(function(){$('.kbl-locator-wrap').each(function(){$(this).data('kblApp', new App($(this)));});});
})(jQuery);
