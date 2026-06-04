(function(){
  const charts = {};
  window.csrfToken = function(){ return $('meta[name="csrf-token"]').attr('content') || ''; };
  window.initDataTable = function(selector){
    const $table = $(selector);
    if (!$table.length || !$.fn.DataTable) return;
    $table.each(function(){
      if ($.fn.DataTable.isDataTable(this)) return;
      $(this).DataTable({pageLength:10,lengthMenu:[5,10,25,50],language:{search:'Suche:',lengthMenu:'_MENU_ Einträge anzeigen',info:'_START_ bis _END_ von _TOTAL_ Einträgen',infoEmpty:'Keine Einträge',zeroRecords:'Keine passenden Einträge gefunden',paginate:{first:'Erste',last:'Letzte',next:'Weiter',previous:'Zurück'}}});
    });
  };
  window.renderChart = function(canvasId,type,labels,datasets,options){
    if (typeof Chart === 'undefined') return;
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    if (charts[canvasId]) charts[canvasId].destroy();
    charts[canvasId] = new Chart(canvas,{type:type,data:{labels:labels,datasets:datasets},options:options || {responsive:true}});
  };
  window.loadModule = function(moduleKey){
    $('#main-content').html('<div class="loading">Modul wird geladen …</div>');
    $.ajax({url:'ajax/load_module.php',method:'POST',headers:{'X-CSRF-Token':window.csrfToken()},data:{module_key:moduleKey,csrf_token:window.csrfToken()},success:function(resp){
      $('#main-content').html(resp.html || '<div class="error">Keine Antwort.</div>');
      $('.module-link').removeClass('active'); $('.module-link[data-module="'+moduleKey+'"]').addClass('active');
      $(document).trigger('module:loaded',[moduleKey]);
    },error:function(xhr){ $('#main-content').html('<div class="error">'+((xhr.responseJSON&&xhr.responseJSON.message)||'Modul konnte nicht geladen werden.')+'</div>'); }});
  };
  window.ajaxForm = function(form, done){
    const $form = $(form);
    $.ajax({url:$form.attr('action'),method:'POST',headers:{'X-CSRF-Token':window.csrfToken()},data:$form.serialize(),success:function(resp){ if(resp.csrf_token){$('meta[name="csrf-token"]').attr('content',resp.csrf_token)}; if(done) done(resp); else if(resp.success) window.loadModule($form.data('reload')||'dashboard');},error:function(xhr){alert((xhr.responseJSON&&xhr.responseJSON.message)||'Fehler beim Speichern.');}});
  };
  $(document).on('submit','form.ajax-form',function(e){e.preventDefault(); window.ajaxForm(this,function(resp){ if(resp.message) alert(resp.message); if(resp.success) window.loadModule($(e.target).data('reload') || $(e.target).find('[name=module_key]').val() || 'dashboard'); });});
  $(document).on('click','.menu-parent',function(){ $(this).closest('.menu-group').toggleClass('open'); });
  $(document).on('click','.module-link',function(e){e.preventDefault(); window.loadModule($(this).data('module'));});
  $(function(){ $('.menu-group:first').addClass('open'); window.loadModule($('#main-content').data('default-module') || 'dashboard'); });
})();
