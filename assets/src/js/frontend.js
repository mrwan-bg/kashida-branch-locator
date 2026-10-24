(function($){
'use strict';

function labels(){ return window.kbl_front_labels || {}; }
function LBL(k,d){ return labels()[k] || d; }
function esc(v){ return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function mapsUrl(b){ return 'https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(b.latitude+','+b.longitude); }
function splitPhoneNumbers(v){
  return String(v==null?'':v).split(/\s+[-–—]\s+|\s*[,;]\s*/).map(function(n){return n.trim();}).filter(Boolean);
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
function searchToggle(c){
  if(!c.search && c.layout!=='map-table')return '';
  var label=LBL('search_label','Search');
  return '<button type="button" class="kbl-search-toggle'+(c.searchOpen?' is-open':'')+'" aria-label="'+esc(label)+'" title="'+esc(label)+'" aria-expanded="'+(c.searchOpen?'true':'false')+'"><span aria-hidden="true">⌕</span></button>';
}
function searchBox(c){
  if(!c.search && c.layout!=='map-table')return '';
  return '<div class="kbl-search-field"><span class="kbl-control-label">'+esc(LBL('search_label','Search branches'))+'</span><div class="kbl-inline-search"><span class="kbl-search-icon" aria-hidden="true">⌕</span><input class="kbl-search" type="search" value="'+esc(c.searchValue||'')+'" placeholder="'+esc(LBL('search_branches','Search branches...'))+'" aria-label="'+esc(LBL('search_label','Search'))+'">'+(c.searchValue?'<button type="button" class="kbl-search-clear" aria-label="'+esc(LBL('clear_search','Clear search'))+'" title="'+esc(LBL('clear_search','Clear search'))+'">×</button>':'')+'</div></div>';
}
function toolbar(c,items,total,shown){
  var hasFilters=c.city_filter||c.group_filter;
  var tableLayout=c.layout==='table'||c.layout==='map-table';
  var hasSearch=c.search&&!tableLayout;
  if(!hasFilters&&!hasSearch)return '';
  var h='<div class="kbl-toolbar-panel">';
  h+='<div class="kbl-toolbar-controls">';
  if(c.search){
    h+=searchBox(c);
  }
  if(c.city_filter){
    h+='<label class="kbl-control"><span class="kbl-control-label">'+esc(LBL('filter_city','City'))+'</span><select class="kbl-filter-city"><option value="">'+esc(LBL('all_cities','All Cities'))+'</option>';
    uniqueValues(items,'city').forEach(function(v){h+='<option value="'+esc(v)+'"'+(String(c.cityValue||'')===v?' selected':'')+'>'+esc(v)+'</option>';});
    h+='</select></label>';
  }
  if(c.group_filter){
    var groups=[];
    items.forEach(function(b){if(b.group_slug&&!groups.some(function(g){return g.slug===b.group_slug;}))groups.push({slug:b.group_slug,name:b.group_name||b.group_slug});});
    groups.sort(function(a,b){return a.name.localeCompare(b.name,undefined,{numeric:true,sensitivity:'base'});});
    h+='<label class="kbl-control"><span class="kbl-control-label">'+esc(LBL('filter_group','Group'))+'</span><select class="kbl-filter-group"><option value="">'+esc(LBL('all_groups','All Groups'))+'</option>';
    groups.forEach(function(g){h+='<option value="'+esc(g.slug)+'"'+(String(c.groupValue||'')===g.slug?' selected':'')+'>'+esc(g.name)+'</option>';});
    h+='</select></label>';
  }
  if(hasActiveControls(c)) h+='<button type="button" class="kbl-reset-controls">'+esc(LBL('reset_filters','Reset'))+'</button>';
  h+='</div>';
  if(!tableLayout) h+='<div class="kbl-toolbar-meta"><span class="kbl-results-count" aria-live="polite">'+esc(formatCount(shown,total))+'</span></div>';
  h+='</div>';
  return h;
}
function info(b,c){
  var h='<div class="kbl-popup"><strong>'+esc(b.name)+'</strong>';
  if(c.show_group&&b.group_name)h+=badge(b);
  if(c.show_city)h+='<div><b>'+esc(LBL('city','City'))+':</b> '+esc(b.city||'—')+'</div>';
  if(c.show_address)h+='<div><b>'+esc(LBL('address','Address'))+':</b> '+esc(b.address||'—')+'</div>';
  if(c.show_phone)h+='<div><b>'+esc(LBL('phone','Phone'))+':</b> '+phone(b.phone)+'</div>';if(b.phone2)h+='<div><b>'+esc(LBL('phone2','Secondary phone'))+':</b> '+phone(b.phone2)+'</div>';
  h+='<div class="kbl-actions">';
  if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';
  if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';
  return h+'</div></div>';
}
function row(b,c){
  var h='<div class="kbl-row" data-id="'+esc(b.id)+'">';
  if(c.show_name)h+='<div class="kbl-name">'+esc(b.name)+'</div>';
  if(c.show_city)h+='<div><b>'+esc(LBL('city','City'))+':</b> '+esc(b.city||'—')+'</div>';
  if(c.show_address)h+='<div><b>'+esc(LBL('address','Address'))+':</b> '+esc(b.address||'—')+'</div>';
  if(c.show_phone)h+='<div><b>'+esc(LBL('phone','Phone'))+':</b> '+phone(b.phone)+'</div>';if(b.phone2)h+='<div><b>'+esc(LBL('phone2','Secondary phone'))+':</b> '+phone(b.phone2)+'</div>';
  if(c.show_group&&b.group_name)h+='<div>'+badge(b)+'</div>';
  if(c.show_call||c.show_directions||b.website){h+='<div class="kbl-actions">';if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';if(b.website)h+='<a class="kbl-action-website" target="_blank" rel="noopener noreferrer" href="'+esc(b.website)+'">'+esc(LBL('visit_website','Visit website'))+'</a>';h+='</div>';}
  return h+'</div>';
}
function table(items,c,total,shown){
  total=typeof total==='number'?total:items.length;
  shown=typeof shown==='number'?shown:items.length;
  var h='<div class="kbl-table-wrap"><div class="kbl-table-headbar">'+searchToggle(c)+'<span class="kbl-table-results" aria-live="polite">'+esc(formatCount(shown,total))+'</span>'+searchBox(c)+'</div><table><thead><tr>';
  function th(label,key,enabled){
    var active=c.sorting&&c.sortKey===key;
    var icon=active?(c.sortOrder==='DESC'?'↓':'↑'):'↕';
    var state=active?(c.sortOrder==='DESC'?LBL('sort_desc','Descending'):LBL('sort_asc','Ascending')):LBL('sort_click','Click to sort');
    if(c.sorting&&enabled)return '<th scope="col"><button type="button" class="kbl-sort-th" data-sort-key="'+key+'" aria-label="'+esc(LBL('sort_by','Sort by')+' '+label+' — '+state)+'" title="'+esc(LBL('sort_by','Sort by')+' '+label+' — '+state)+'"><span class="kbl-sort-label">'+esc(label)+'</span><span class="kbl-sort-icon" aria-hidden="true">'+icon+'</span></button></th>';
    return '<th scope="col">'+esc(label)+'</th>';
  }
  if(c.show_name)h+=th(LBL('branch','Branch'),'name',true);
  if(c.show_city)h+=th(LBL('city','City'),'city',true);
  if(c.show_address)h+=th(LBL('address','Address'),'address',false);
  if(c.show_phone)h+=th(LBL('phone','Phone'),'phone',false);
  if(c.show_group)h+=th(LBL('group','Group'),'group_name',true);
  if(c.show_call||c.show_directions)h+='<th scope="col" class="kbl-table-actions-head"></th>';
  h+='</tr></thead>';
  if(items.length){
    h+='<tbody>';
    items.forEach(function(b){
      h+='<tr class="kbl-row" data-id="'+esc(b.id)+'">';
      if(c.show_name)h+='<td data-label="'+esc(LBL('branch','Branch'))+'"><strong>'+esc(b.name)+'</strong></td>';
      if(c.show_city)h+='<td data-label="'+esc(LBL('city','City'))+'">'+esc(b.city||'—')+'</td>';
      if(c.show_address)h+='<td data-label="'+esc(LBL('address','Address'))+'">'+esc(b.address||'—')+'</td>';
      if(c.show_phone)h+='<td data-label="'+esc(LBL('phone','Phone'))+'">'+phone(b.phone)+'</td>';
      if(c.show_group)h+='<td data-label="'+esc(LBL('group','Group'))+'">'+badge(b)+'</td>';
      if(c.show_call||c.show_directions){h+='<td class="kbl-table-actions" data-label="">';if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';h+='</td>';}
      h+='</tr>';
    });
    h+='</tbody>';
  }else{
    h+='<tbody><tr><td class="kbl-table-empty" colspan="10">'+esc(LBL('no_results','No branches found.'))+'</td></tr></tbody>';
  }
  return h+'</table></div>';
}
function card(b,c){
  var h='<article class="kbl-card kbl-row" data-id="'+esc(b.id)+'" style="--kbl-group-color:'+esc(b.group_color||'#315bea')+';">';
  h+='<div class="kbl-card-content">';
  if(c.show_group&&b.group_name)h+='<div class="kbl-card-group">'+badge(b)+'</div>';
  if(c.show_name)h+='<h3 class="kbl-card-title">'+esc(b.name)+'</h3>';
  h+='<div class="kbl-card-body">';
  if(c.show_city)h+='<div class="kbl-card-field"><span>'+esc(LBL('city','City'))+'</span><strong>'+esc(b.city||'—')+'</strong></div>';
  if(c.show_address)h+='<div class="kbl-card-field"><span>'+esc(LBL('address','Address'))+'</span><strong>'+esc(b.address||'—')+'</strong></div>';
  if(c.show_phone)h+='<div class="kbl-card-field"><span>'+esc(LBL('phone','Phone'))+'</span><strong>'+phone(b.phone)+'</strong></div>';if(b.phone2)h+='<div class="kbl-card-field"><span>'+esc(LBL('phone2','Secondary phone'))+'</span><strong>'+phone(b.phone2)+'</strong></div>';
  h+='</div></div>';
  if(c.show_call||c.show_directions||b.website){h+='<div class="kbl-actions">';if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';if(b.website)h+='<a class="kbl-action-website" target="_blank" rel="noopener noreferrer" href="'+esc(b.website)+'">'+esc(LBL('visit_website','Visit website'))+'</a>';h+='</div>';}
  return h+'</article>';
}
function cards(items,c){if(!items.length)return '<div class="kbl-empty">'+esc(LBL('no_results','No branches found.'))+'</div>';if(!c.group_cards){var plain='<div class="kbl-cards">';items.forEach(function(b){plain+=card(b,c);});return plain+'</div>';}var groups={},order=[];items.forEach(function(b){var key=b.group_slug||'__ungrouped__';if(!groups[key]){groups[key]={name:b.group_name||LBL('ungrouped','Other branches'),color:b.group_color||'#0a6b70',items:[]};order.push(key);}groups[key].items.push(b);});var h='<div class="kbl-card-groups">';order.forEach(function(key){var g=groups[key];h+='<section class="kbl-card-group-section"><h2 class="kbl-card-group-heading"'+(g.color?' style="--kbl-section-color:'+esc(g.color)+';"':'')+'>'+esc(g.name)+'</h2><div class="kbl-cards">';g.items.forEach(function(b){h+=card(b,c);});h+='</div></section>';});return h+'</div>';}
function accordion(items,c){
  if(!items.length)return '<div class="kbl-empty">'+esc(LBL('no_results','No branches found.'))+'</div>';
  var groups={};
  items.forEach(function(b){var key=b.group_slug||'__ungrouped__';if(!groups[key])groups[key]={name:b.group_name||'',color:b.group_color||'#315bea',items:[]};groups[key].items.push(b);});
  var h='<div class="kbl-accordion">';
  Object.keys(groups).forEach(function(key,index){var g=groups[key],open=index===0;h+='<section class="kbl-acc" style="--kbl-group-color:'+esc(g.color)+';"><button type="button" aria-expanded="'+(open?'true':'false')+'"><span class="kbl-acc-title">'+(g.name?'<span class="kbl-acc-badge" style="background:'+esc(g.color)+'">'+esc(g.name)+'</span>':'<span>'+esc(LBL('group','Group'))+'</span>')+' <small>('+g.items.length+')</small></span><span class="kbl-acc-chevron">⌄</span></button><div class="kbl-acc-content"'+(open?' style="display:block"':'')+'>';g.items.forEach(function(b){h+='<div class="kbl-acc-branch kbl-row" data-id="'+esc(b.id)+'">';if(c.show_name)h+='<strong>'+esc(b.name)+'</strong>';if(c.show_city)h+='<div>⌖ '+esc(b.city||'—')+'</div>';if(c.show_address)h+='<div>⌖ '+esc(b.address||'—')+'</div>';if(c.show_phone)h+='<div>☎ '+phone(b.phone)+'</div>';if(c.show_call||c.show_directions||b.website){h+='<div class="kbl-actions">';if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';if(b.website)h+='<a class="kbl-action-website" target="_blank" rel="noopener noreferrer" href="'+esc(b.website)+'">'+esc(LBL('visit_website','Visit website'))+'</a>';h+='</div>';}h+='</div>';});h+='</div></section>';});
  return h+'</div>';
}
function list(items,c){
  if(!items.length)return '<div class="kbl-empty">'+esc(LBL('no_results','No branches found.'))+'</div>';
  var h='<div class="kbl-list-view">';
  items.forEach(function(b){
    var color=esc(b.group_color||'#f05c41');
    h+='<article class="kbl-list-item kbl-row" data-id="'+esc(b.id)+'" style="--kbl-group-color:'+color+';">';
    h+='<div class="kbl-list-main">';
    if(c.show_name)h+='<h3 class="kbl-list-title">'+esc(b.name)+'</h3>';
    if(c.show_group&&b.group_name)h+='<div class="kbl-list-group">'+badge(b)+'</div>';
    h+='</div>';
    h+='<div class="kbl-list-details">';
    if(c.show_city)h+='<div class="kbl-list-field"><span>'+esc(LBL('city','City'))+'</span><strong>'+esc(b.city||'—')+'</strong></div>';
    if(c.show_address)h+='<div class="kbl-list-field"><span>'+esc(LBL('address','Address'))+'</span><strong>'+esc(b.address||'—')+'</strong></div>';
    if(c.show_phone)h+='<div class="kbl-list-field"><span>'+esc(LBL('phone','Phone'))+'</span><strong>'+phone(b.phone)+'</strong></div>';
    h+='</div>';
    if(c.show_call||c.show_directions){
      h+='<div class="kbl-list-actions kbl-actions">';
      if(c.show_call&&b.phone)h+='<a class="kbl-action-call" href="tel:'+esc(primaryPhone(b.phone))+'">'+esc(LBL('call','Call'))+'</a>';
      if(c.show_directions)h+='<a class="kbl-action-directions" target="_blank" rel="noopener" href="'+mapsUrl(b)+'">'+esc(LBL('directions','Directions'))+'</a>';
      h+='</div>';
    }
    h+='</article>';
  });
  return h+'</div>';
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
  this.el.innerHTML='<div class="kbl-map-viewport"><div class="kbl-map-tiles"></div><div class="kbl-map-markers"></div><div class="kbl-map-popup-layer"></div></div><div class="kbl-map-controls"><button type="button" class="kbl-zi" aria-label="'+esc(LBL('zoom_in','Zoom in'))+'" title="'+esc(LBL('zoom_in','Zoom in'))+'">+</button><button type="button" class="kbl-zo" aria-label="'+esc(LBL('zoom_out','Zoom out'))+'" title="'+esc(LBL('zoom_out','Zoom out'))+'">−</button></div><div class="kbl-map-attribution">© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors</div>';
  this.viewport=this.el.querySelector('.kbl-map-viewport');this.tiles=this.el.querySelector('.kbl-map-tiles');this.layer=this.el.querySelector('.kbl-map-markers');this.popupLayer=this.el.querySelector('.kbl-map-popup-layer');
  this.dragging=false;this.dragStart=null;this.touchStart=null;this.touchActive=false;
  this.touchHint=document.createElement('div');this.touchHint.className='kbl-map-touch-hint';this.touchHint.setAttribute('aria-hidden','true');this.touchHint.textContent=LBL('map_touch_hint','Use two fingers to move the map');this.el.appendChild(this.touchHint);
  var self=this;this.el.querySelector('.kbl-zi').onclick=function(){self.setZoom(self.zoom+1)};this.el.querySelector('.kbl-zo').onclick=function(){self.setZoom(self.zoom-1)};window.addEventListener('resize',function(){self.draw();});
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
  window.addEventListener('mousemove',function(e){
    if(!self.dragging)return;
    var dx=e.clientX-self.dragStart.x,dy=e.clientY-self.dragStart.y,cp=self.dragStart.center;
    self.center=self.unproject(cp.x-dx,cp.y-dy,self.zoom);self.draw();
  });
  window.addEventListener('mouseup',function(){
    if(!self.dragging)return;
    self.dragging=false;self.viewport.classList.remove('is-dragging');
  });
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
MapView.prototype.add=function(b){var self=this,el=document.createElement('button');el.type='button';el.className='kbl-marker';el.title=b.name;el.setAttribute('aria-label',b.name);el.innerHTML='<span></span>';el.onclick=function(e){e.stopPropagation();self.app.select(b.id,true);};this.layer.appendChild(el);this.markers[String(b.id)]={b:b,el:el};};
MapView.prototype.clear=function(){this.layer.innerHTML='';this.popupLayer.innerHTML='';this.markers={};this.popup=null;};
MapView.prototype.open=function(b){var m=this.markers[String(b.id)];if(!m)return;this.close();var p=document.createElement('div');p.className='kbl-map-popup';p.innerHTML=info(b,this.c)+'<button class="kbl-close" type="button" aria-label="'+esc(LBL('close','Close'))+'" title="'+esc(LBL('close','Close'))+'">×</button>';p.querySelector('.kbl-close').onclick=this.close.bind(this);this.popupLayer.appendChild(p);this.popup={b:b,el:p};this.draw();};
MapView.prototype.close=function(){if(this.popup&&this.popup.el)this.popup.el.remove();this.popup=null;};
MapView.prototype.draw=function(){if(!this.el.clientWidth||!this.el.clientHeight)return;var w=this.el.clientWidth,h=this.el.clientHeight,cp=this.project(this.center[0],this.center[1],this.zoom),startX=Math.floor((cp.x-w/2)/256)-1,endX=Math.floor((cp.x+w/2)/256)+1,startY=Math.floor((cp.y-h/2)/256)-1,endY=Math.floor((cp.y+h/2)/256)+1,n=Math.pow(2,this.zoom);this.tiles.innerHTML='';for(var y=startY;y<=endY;y++){if(y<0||y>=n)continue;for(var x=startX;x<=endX;x++){var wx=((x%n)+n)%n,img=document.createElement('img');img.src='https://tile.openstreetmap.org/'+this.zoom+'/'+wx+'/'+y+'.png';img.alt='';img.className='kbl-tile';img.style.left=(x*256-cp.x+w/2)+'px';img.style.top=(y*256-cp.y+h/2)+'px';this.tiles.appendChild(img);}}var self=this;Object.keys(this.markers).forEach(function(id){var m=self.markers[id],p=self.project(m.b.latitude,m.b.longitude,self.zoom);m.el.style.left=(p.x-cp.x+w/2-11)+'px';m.el.style.top=(p.y-cp.y+h/2-28)+'px';});if(this.popup){var p=this.project(this.popup.b.latitude,this.popup.b.longitude,this.zoom);this.popup.el.style.left=(p.x-cp.x+w/2-150)+'px';this.popup.el.style.top=(p.y-cp.y+h/2-185)+'px';}};

function App($root){
  this.$root=$root;this.root=$root[0];this.c=JSON.parse($root.attr('data-config')||'{}');
  this.c.sortKey=this.c.sortKey||'name';this.c.sortOrder=this.c.sortOrder==='DESC'?'DESC':'ASC';
  this.all=Array.isArray(this.c.items)?this.c.items:[];this.page=1;this.map=null;this.loadingMore=false;this.bind();this.render();
}
App.prototype.bind=function(){
  var self=this;
  this.$root.on('input','.kbl-search',function(){
    var input=this;
    var start=typeof input.selectionStart==='number'?input.selectionStart:null;
    var end=typeof input.selectionEnd==='number'?input.selectionEnd:null;
    var direction=input.dir||'';
    self.c.searchValue=$(input).val()||'';
    self.page=1;
    self.render();
    var next=self.$root.find('.kbl-search').get(0);
    if(next){
      next.focus({preventScroll:true});
      if(start!==null&&end!==null){
        var len=String(next.value||'').length;
        next.setSelectionRange(Math.min(start,len),Math.min(end,len));
      }
      if(direction)next.dir=direction;
    }
  });
  this.$root.on('click','.kbl-search-toggle',function(){self.c.searchOpen=!self.c.searchOpen;self.render();if(self.c.searchOpen){var input=self.$root.find('.kbl-search').get(0);if(input)input.focus({preventScroll:true});}});
  this.$root.on('click','.kbl-search-clear',function(){self.c.searchValue='';self.c.searchOpen=true;self.page=1;self.render();var input=self.$root.find('.kbl-search').get(0);if(input)input.focus({preventScroll:true});});
  this.$root.on('change','.kbl-filter-city',function(){self.c.cityValue=$(this).val()||'';self.page=1;self.render();});
  this.$root.on('change','.kbl-filter-group',function(){self.c.groupValue=$(this).val()||'';self.page=1;self.render();});
  this.$root.on('click','.kbl-sort-th',function(){var k=$(this).data('sort-key');if(self.c.sortKey===k)self.c.sortOrder=self.c.sortOrder==='DESC'?'ASC':'DESC';else{self.c.sortKey=k;self.c.sortOrder='ASC';}self.page=1;self.render();});
  this.$root.on('click','.kbl-reset-controls',function(){self.c.searchValue='';self.c.searchOpen=false;self.c.cityValue='';self.c.groupValue='';self.c.sortKey='name';self.c.sortOrder='ASC';self.c.searchOpen=true;self.page=1;self.render();});
  this.$root.on('click','.kbl-page',function(){self.page=parseInt($(this).data('page'),10)||1;self.render();});
  $(window).on('scroll.kblInfinite'+(this.root.id||Math.random().toString(36).slice(2)),function(){
    if(self.c.display_mode!=='infinite_scroll'||!self.c.pagination||self.loadingMore)return;
    var items=visibleRows(self.all,self.c),pages=Math.max(1,Math.ceil(items.length/Math.max(1,self.c.per_page||15)));
    if(self.page>=pages)return;
    var rect=self.root.getBoundingClientRect();
    if(rect.bottom < window.innerHeight+240){self.loadingMore=true;self.page++;self.render();self.loadingMore=false;}
  });
  this.$root.on('click','.kbl-row[data-id]',function(e){if($(e.target).closest('a,button').length)return;self.select($(this).data('id'),false);});
  this.$root.on('click','.kbl-acc>button',function(){var x=$(this).closest('.kbl-acc'),open=!x.hasClass('open');x.toggleClass('open',open);$(this).attr('aria-expanded',open?'true':'false');x.find('.kbl-acc-content').first().stop(true,true).slideToggle(160);});
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
    if(isFinite(parseFloat(b.latitude))&&isFinite(parseFloat(b.longitude))){self.map.center=[b.latitude,b.longitude];self.map.open(b);self.map.draw();}
    if(!fromMarker){var r=this.$root.find('.kbl-row[data-id="'+id+'"]').get(0);if(r)r.scrollIntoView({behavior:'smooth',block:'nearest'});}
  }
};
App.prototype.render=function(){
  var c=this.c,items=visibleRows(this.all,c),per=Math.max(1,c.per_page||15),totalPages=c.pagination?Math.max(1,Math.ceil(items.length/per)):1;
  if(this.page>totalPages)this.page=totalPages;
  var infinite=c.display_mode==='infinite_scroll'&&c.pagination;
  var shown=c.pagination?(infinite?items.slice(0,this.page*per):items.slice((this.page-1)*per,this.page*per)):items;
  var layout=c.layout||'table';
  this.$root.find('.kbl-toolbar').html(toolbar(c,this.all,items.length,shown.length));
  var html=layout==='table'?table(shown,c,items.length,shown.length):layout==='cards'?cards(shown,c):layout==='accordion'?accordion(shown,c):layout==='list'?list(shown,c):'';
  if(layout==='map')this.$root.find('.kbl-results').html('');
  else if(layout==='map-table')this.$root.find('.kbl-results').html(table(shown,c,items.length,shown.length));
  else this.$root.find('.kbl-results').html(html);
  if(infinite){this.$root.find('.kbl-pagination').empty();if(this.page<totalPages)this.$root.find('.kbl-pagination').html('<span class="kbl-pagination-label">'+esc(LBL('loading_more','Scroll to load more'))+'</span>');}
  else paginate(this.$root.find('.kbl-pagination'),this.page,totalPages,c);
  if(layout==='map'||layout==='map-table')this.renderMap(items);
};
App.prototype.renderMap=function(items){
  var self=this;
  if(!this.map){var el=this.$root.find('.kbl-map').get(0);if(!el)return;this.map=new MapView(el,this.c,this);}
  this.map.clear();var pts=[];
  items.forEach(function(b){var lat=parseFloat(b.latitude),lng=parseFloat(b.longitude);if(!isFinite(lat)||!isFinite(lng)||lat===0&&lng===0)return;self.map.add(b);pts.push([lat,lng]);});
  this.map.fit(pts);
  var notice=this.$root.find('.kbl-map-empty');
  if(!pts.length){if(!notice.length)this.$root.find('.kbl-map').append('<div class="kbl-map-empty">'+esc(LBL('no_coordinates','No valid coordinates are available for these branches.'))+'</div>');}
  else notice.remove();
};
$(function(){$('.kbl-locator-wrap').each(function(){$(this).data('kblApp', new App($(this)));});});
})(jQuery);
