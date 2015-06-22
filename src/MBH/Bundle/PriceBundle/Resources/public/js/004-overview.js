/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#room-overview-table-wrapper'),
                begin = $('#room-overview-filter-begin'),
                end = $('#room-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#room-overview-filter-roomType').val(),
                    'tariffs': $('#room-overview-filter-tariff').val()
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('room_overview_table'),
                    data: data,
                    beforeSend: function () { pricesProcessing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        pricesProcessing = false;
                        $('td.alert').each(function () {
                            $("td[data-id='" + $(this).attr('data-id') + "']").addClass('alert');
                        });
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.room-overview-filter').change(function () {
        showTable();
    });
});