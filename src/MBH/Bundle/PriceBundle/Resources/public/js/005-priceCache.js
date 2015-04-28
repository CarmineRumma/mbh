/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#price-cache-overview-table-wrapper'),
                begin = $('#price-cache-overview-filter-begin'),
                end = $('#price-cache-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#price-cache-overview-filter-roomType').val(),
                    'tariffs': $('#price-cache-overview-filter-tariff').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input, span.disabled-detector');
                    input.closest('td').click(function () {
                        var td = $("td[data-id='" + $(this).attr('data-id') + "']"),
                            field = $(this).children('span.input').children('input[disabled]');

                        td.children('span.input').children('input').removeAttr('disabled');

                        if (field.prop('type') === 'checkbox') {
                            field.prop('checked', !field.prop('checked')).css('checkbox-end');
                        } else {
                            field.focus();
                        }
                        td.children('span.input').children('span.disabled-detector').remove();
                    });
                    input.change(function () {
                        if (this.value === '') {
                            return;
                        }
                        var value = parseFloat(this.value);
                        if (value < 0 || isNaN(value)) {
                            this.value = 0;
                        }
                    });
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('price_cache_overview_table'),
                    data: data,
                    beforeSend: function () { pricesProcessing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#price-cache-overview-begin').val());
                        end.val($('#price-cache-overview-end').val());
                        inputs();
                        pricesProcessing = false;
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.price-cache-overview-filter').change(function () {
        showTable();
    });

    //generator
    (function () {
        var prices = $('input.delete-prices'),
            showMessage = function () {
                prices.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? 'Цена будет удалена' : '';
                    $(this).closest('.col-md-4').
                        next('.col-md-6').
                        html('<span class="text-danger text-left input-errors">' + text +  '</span>');
                });
            };
        showMessage();
        prices.change(showMessage);
    }());
});
