<div class="input-group col-6 col-sm-4 col-md-2">
  <input class="form-control form-control-sm" type="text" id="dt-search" placeholder="Filtrar...">
  <div class="input-group-append">
    <button class="btn btn-sm btn-outline-secondary" id="dt-search-clear">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>

@section('javascripts_bottom')
@parent
<script>
  $(document).ready(function() {
    $('#dt-search').focus();

    // recuperando o storage local
    var datatableFilter = localStorage.getItem('datatableFilter');
    $('#dt-search').val(datatableFilter);

    // vamos aplicar o filtro
    oTable.search($('#dt-search').val()).draw();
    $('.datatable-counter').html(oTable.page.info().recordsDisplay);

    // vamos filtrar à medida que digita
    $('#dt-search').keyup(function() {
      oTable.search($(this).val()).draw();
      $('.datatable-counter').html(oTable.page.info().recordsDisplay);

      // vamos guardar no storage à medida que digita
      localStorage.setItem('datatableFilter', $(this).val());
    });

    // vamos limpar o filtro de busca
    $('#dt-search-clear').on('click', function() {
      $('#dt-search').val('').trigger('keyup');
      $('#dt-search').focus();
    });
  });
</script>
@endsection
