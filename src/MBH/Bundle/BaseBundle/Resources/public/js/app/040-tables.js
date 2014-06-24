/*global window */
$(document).ready(function() {
    'use strict';

    //Select row
    $('table.table-striped').on('click', 'tbody tr', function() {
        $(this).siblings().removeClass('info');
        $(this).toggleClass('info');
    });

    //Dblclick href
    $('table.table-striped').on('dblclick', 'tbody tr', function() {
        var link = $(this).find('a[rel="main"]');

        if (link.length) {
            window.location.href = link.attr('href');
        }
    });

    var table = $('table.table-striped').dataTable({
        "pageLength": 50,
        "stateSave": true,
        language: {
            "sProcessing": "Подождите...",
            "sLengthMenu": "Показать _MENU_ записей",
            "sZeroRecords": "Записи отсутствуют.",
            "sInfo": "Записи с _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записи с 0 до 0 из 0 записей",
            "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
            "sInfoPostFix": "",
            "sSearch": "Поиск ",
            "sUrl": "",
            "oPaginate": {
                "sFirst": "Первая",
                "sPrevious": "Назад",
                "sNext": "Вперед",
                "sLast": "Последняя"
            },
            "oAria": {
                "sSortAscending": ": активировать для сортировки столбца по возрастанию",
                "sSortDescending": ": активировать для сортировки столбцов по убыванию"
            }
        }
    });
    /*new $.fn.dataTable.FixedHeader(table, {
        offsetTop: 50
    });
    var tt = new $.fn.dataTable.TableTools( table , {
        "sSwfPath": "/bundles/maxibookingbase/js/datatables/swf/copy_csv_xls.swf",
        "aButtons": [
                {
                    "sExtends": "copy",
                    "sButtonText": '<i class="fa fa-files-o"></i> Скопировать'
                },
                {
                    "sExtends": "csv",
                    "sButtonText": '<i class="fa fa-file-text-o"></i> CSV'
                },
                {
                    "sExtends": "xls",
                    "sButtonText": '<i class="fa fa-table"></i> Excel'
                },
                {
                    "sExtends": "pdf",
                    "sButtonText": "PDF "
                },
                {
                    "sExtends": "print",
                    "sButtonText": '<i class="fa fa-print"></i> Печать'
                },
            ]
    });
    
    //$( tt.fnContainer() ).insertInto('div.dataTables_wrapper');
    $('#list-export').append($(tt.fnContainer()));
    $('#list-export').find('a').addClass('navbar-btn');*/
});



