/* ===========================================================
   🌿 Open Donasi - Global DataTables Initializer
   Versi: Bootstrap 5 Styled + Responsive + Bahasa Indonesia
   =========================================================== */

$(document).ready(function () {
  // Cek apakah ada tabel dengan class .dataTable di halaman
  if ($('table.dataTable').length > 0) {
    $('table.dataTable').each(function () {
      const table = $(this).DataTable({
        responsive: {
          details: {
            type: 'column',
            target: 0 // kolom pertama = tombol collapsible “+”
          }
        },
        columnDefs: [
          { className: 'dtr-control', orderable: false, targets: 0 }
        ],
        autoWidth: false,
        order: [], // tidak ada sorting default
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        drawCallback: function () {
          // Tambahkan class hover ringan
          $(this.api().table().body()).find('tr').hover(
            function () {
              $(this).addClass('table-active');
            },
            function () {
              $(this).removeClass('table-active');
            }
          );
        }
      });
    });
  }
});

//  $(document).ready(function() {
//             $('#donasiTable').DataTable({
//                 responsive: {
//                     details: {
//                         type: 'column',
//                         target: 'tr'
//                     }
//                 },
//                 columnDefs: [{
//                     className: 'dtr-control',
//                     orderable: false,
//                     targets: 0
//                 }],
//                 order: [1, 'asc'],
//                 autoWidth: false,
//                 pageLength: 10,
//                 lengthMenu: [5, 10, 25, 50],
//                 language: {
//                     url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
//                 }
//             });
//         });