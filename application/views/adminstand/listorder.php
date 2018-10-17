<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div style="padding: 0px;" class="menujudul">
              STOCK MASUK
          </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-md-5 col-sm-12">
      <div class="form-group">
          <label>Nama Barang</label>
          <input type="text" class="form-control" id="namabarang" placeholder="Nama Barang">
      </div>
    </div>
    <div class="col-md-5 col-sm-12">
      <div class="form-group">
          <label>Jumlah Order</label>
          <input type="text" class="form-control numeric" id="jumlahorder" placeholder="Jumlah Order">
      </div>
    </div>
    <div class="col-md-2 col-sm-12">
      <label for="usr">Action</label>
      <button class="btn btn-success" id="buttontambahstok" onclick="tambahorder()">Tambah Order</button>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12 col-sm-12">
      <table id="mytable" class="table table-striped table-bordered">
            <thead class="thead-dark">
              <tr>
                <th style="width: 15%;">ID Produk</th>
                <th style="width: 25%;">Nama Produk</th>
                <th style="width: 15%;">Jumlah Order</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%">Status</th>
                <th style="width: 15%">Batal</th>
                <!-- <th style="width: 12.5%;">Edit</th> -->
              </tr>
            </thead>
        </table>
    </div>
    
  </div>
</div>
<!-- 
<div class="modal fade" id="modal_edit" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="header modal-header">
                <h4 class="modal-title">Edit</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="editid" class=" form-control-label">Stok Masuk</label>
                            <input type="text" id="editsm" placeholder="Masukkan Stok Masuk" class="form-control numeric">
                            <input type="hidden" name="id_lama" id="id_lama">
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">Batal</button>
                <button type="button" onclick="simpanedit()" class="btn add_field_button btn-info">Simpan</button>
            </div>
        </div>
    </div>
</div> -->


<script src=<?php echo base_url("assets/js/jquery.min.js")?>></script>
<script src=<?php echo base_url("assets/js/lib/vector-map/jquery.vmap.js")?>></script>
<script src=<?php echo base_url("assets/js/lib/vector-map/jquery.vmap.min.js")?>></script>
<script src=<?php echo base_url("assets/js/lib/vector-map/jquery.vmap.sampledata.js")?>></script>
<script src=<?php echo base_url("assets/js/lib/vector-map/country/jquery.vmap.world.js")?>></script>
<script src=<?php echo base_url("assets/datatable/datatables.js")?>></script>
<script src=<?php echo base_url("assets/js/popper.min.js"); ?>></script>
<script src=<?php echo base_url("assets/js/plugins.js"); ?>></script>
<script src=<?php echo base_url("assets/js/lib/chosen/chosen.jquery.min.js"); ?>></script>
<script src=<?php echo base_url("assets/datatable/Buttons-1.5.2/js/dataTables.buttons.js")?>></script>
<script src=<?php echo base_url("assets/datatable/Buttons-1.5.2/js/buttons.print.js")?>></script>
<script src=<?php echo base_url("assets/datatable/Buttons-1.5.2/js/buttons.html5.js")?>></script>
<script src=<?php echo base_url("assets/datatable/Buttons-1.5.2/js/buttons.flash.js")?>></script>
<script src=<?php echo base_url("assets/datatable/JSZip-2.5.0/jszip.js")?>></script>
<script src=<?php echo base_url("assets/datatable/pdfmake-0.1.36/pdfmake.js")?>></script>
<script src=<?php echo base_url("assets/datatable/pdfmake-0.1.36/vfs_fonts.js")?>></script>
<script src=<?php echo base_url("assets/vendors/jsPDF-1.3.2/dist/jspdf.debug.js")?>></script>
<script src=<?php echo base_url("assets/vendors/pdfmake-master/build/pdfmake.min.js")?>></script>
<script src=<?php echo base_url("assets/vendors/pdfmake-master/build/vfs_fonts.js")?>></script>
<script src=<?php echo base_url("assets/js/jquery.easy-autocomplete.js")?>></script>
</body>
</html>
<script type="text/javascript">
var id = '';
var option = {
  url : "<?php echo base_url('adminstand/getnamabarang');?>",
  getValue: function(element) {
    console.log(element);
    return element.nama_bahan_jadi;
  },
  list :{
    maxNumberOfElements: 10,
    showAnimation:{
      type:"fade",
      time:400,
      callback:function(){}
    },
    hideAnimation:{
      type:"slide",
      time:400,
      callback:function(){}
    },
    match: {
      enabled: true
    },
    onClickEvent: function() {
      var bahan = $('#namabarang').getSelectedItemData().nama_bahan_jadi;

      $('#namabarang').val(bahan).trigger("change");
    }  
  }
}

$('#namabarang').change(function(){  
  var data = $('#namabarang').val();
  if (data == '') {
    $('#namabarang').removeClass("is-invalid");
  }else{
    $.ajax({
            type:"post",
            url: "<?php echo base_url('adminstand/getnamabarang')?>/",
            dataType:"json",
            success:function(list)
            {
              // console.log(list);
              var found = false;
              for (var i = list.length - 1; i >= 0; i--) {
                if (data==list[i].nama_bahan_jadi) {
                  found = true;
                  id = list[i].id_bahan_jadi;

                  if ($('#namabarang').has("is-invalid")) {
                    $('#namabarang').removeClass("is-invalid");
                  }
                }

                if (!found) {
                  id = 'unidentified';
                  $('#namabarang').addClass("is-invalid");
                }
              }

        // if (found) {
        //  alert('ketemu');
        // }else{
        //  alert('ga ketemu');
        // }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
              alert(errorThrown);
            }
        }
      );
  }
  
});

$('.numeric').on('input', function (event) { 
    this.value = this.value.replace(/[^0-9]/g, '');
});

$("#namabarang").easyAutocomplete(option);

function tambahorder() {
  var id_bahan = id;
  var nama = $('#namabarang').val();
  var jumlahorder = $('#jumlahorder').val();

  if (id_bahan == 'unidentified') {
    $('#namabarang').addClass("is-invalid");
  }

  if ($('#jumlahorder').val() == '') {
    $('#jumlahorder').addClass("is-invalid");
  }

  if (id_bahan != 'unidentified' && $('#jumlahorder').val() != '') {
    $.ajax(
            {
                type:"post",
                url: "<?php echo base_url('adminstand/tambah_stok_masuk')?>/",
                data:{ id:id_bahan,nama:nama,jumlahorder:jumlahorder},
                success:function(response)
                {
                  $("#namabarang").val('');
                    $("#jumlahorder").val('');
                    reload_table();

                  if(response == 'Berhasil Ditambahkan'){
                    
                    
                    if($('#namabarang').has("is-invalid")){
                      $('#namabarang').removeClass("is-invalid");
                    }

                    if($('#jumlahorder').has("is-invalid")){
                      $('#jumlahorder').removeClass("is-invalid");
                    }

                    $("#namabarang").focus();

                    alert(response);
                  }else if(response =='Data telah di update!.'){
                    
                    alert(response);
                  }else{
                    alert('unknown error is happen! try again.');
                  }
                  
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                  alert(errorThrown);
                }
            }
        );
  }
}

jQuery( document ).ready(function( $ ) {
    $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
    {
      return {
        "iStart": oSettings._iDisplayStart,
        "iEnd": oSettings.fnDisplayEnd(),
        "iLength": oSettings._iDisplayLength,
        "iTotal": oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
        "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
      };
    };
    tabeldata = $("#mytable").dataTable({
      initComplete: function() {
        var api = this.api();
        $('#mytable_filter input')
        .on('.DT')
        .on('keyup.DT', function(e) {
          if (e.keyCode == 13) {
            api.search(this.value).draw();
          }
        });
      },
      oLanguage: {
        sProcessing: "loading..."
      },
      responsive: true,
      serverSide: true,
      ajax: {
    "type"   : "POST",
    "url"    : "<?php echo base_url('adminstand/datajumlahorder');?>",
    "dataSrc": function (json) {
      var return_data = new Array();
      for(var i=0;i< json.data.length; i++){
        return_data.push({
          // 'id_bahan_jadi': json.data[i].id_bahan_jadi,
          // 'nama_bahan_jadi'  : json.data[i].nama_bahan_jadi,
          // 'stok_masuk' : json.data[i].stok_masuk,
          // 'tanggal' : json.data[i].tanggal,
          // 'edit' : '<button onclick="editSM(\''+json.data[i].id_bahan_jadi.split(' ').join('+')+'\',\''+json.data[i].stok_masuk+'\',\''+json.data[i].tanggal+'\')" class="btn btn-warning" style="color:white;">Edit</button> '
        })
      }
      return return_data;
    }
  },
   dom: 'Bfrtlip',
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                filename: 'Produk Data',
                exportOptions: {
                  columns:[0,1,2,3]
                }
            },{
                extend: 'excelHtml5',
                text: 'Excel',
                className: 'exportExcel',
                filename: 'Produk Data',
                exportOptions: {
                  columns:[0,1,2,3]
                }
            },{
                extend: 'csvHtml5',
                filename: 'Produk Data',
                exportOptions: {
                  columns:[0,1,2,3]
                }
            },{
                extend: 'pdfHtml5',
                filename: 'Produk Data',
                exportOptions: {
                  columns:[0,1,2,3]
                }
            },{
                extend: 'print',
                filename: 'Produk Data',
                exportOptions: {
                  columns:[0,1,2,3]
                }
            }
        ],
        "lengthChange": true,
  columns: [
    {'data': 'id_barang'},
    {'data': 'nama_barang'},
    {'data': 'jumlah_order'},
    {'data': 'tanggal'},
    {'data': 'status'},
    {'data': 'batal'},
    // {'data': 'edit','orderable':false,'searchable':false}
  ],
      rowCallback: function(row, data, iDisplayIndex) {
        var info = this.fnPagingInfo();
        var page = info.iPage;
        var length = info.iLength;
        var index = page * length + (iDisplayIndex + 1);
        // $('td:eq(0)', row).html(index);
      }
    });
});
function reload_table(){
  tabeldata.api().ajax.reload(null,false);
}

</script>