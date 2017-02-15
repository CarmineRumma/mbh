/*jslint todo: true */
/*global window, $, document, Routing */
$(document).ready(function () {
    'use strict';

    // set user Date (ClientConfig)
    if ($('.daterangepicker-input').prev().is('#windows-report-filter-begin')) {

        if (!($('#windows-report-filter-begin').val()) && !($('#windows-report-filter-end').val())) {
            $('.daterangepicker-input').data('daterangepicker').setStartDate(moment(mbh.startDatePick, "DD.MM.YYYY").toDate());
            $('.daterangepicker-input').data('daterangepicker').setEndDate(moment(mbh.startDatePick, "DD.MM.YYYY").add(45, 'days').toDate());
            $('#windows-report-filter-begin').val($('.daterangepicker-input').data('daterangepicker').startDate.format('DD.MM.YYYY'));
            $('#windows-report-filter-end').val($('.daterangepicker-input').data('daterangepicker').endDate.format('DD.MM.YYYY'));
        }
    }

    //table
    var packageData = null,
        choosePackages = function () {

            $('.tile-bookable').find('.date').hover(function () {
                $(this).children('div').show();
            }, function () {
                if (!$(this).hasClass('selected-date-row')) {
                    $(this).children('div').hide();
                }
            });
            $('.tile-bookable').click(function () {
                var td = $(this),
                    roomId = td.attr('data-room-id'),
                    date =  td.attr('data-date');
                if (packageData && roomId === packageData.room.id && packageData.dateOne !== date) {
                    // create packages
                    packageData.dataTwo = date;
                    //TODO: create packages
                    packageData = null;
                } else {

                    $('.date').removeClass('selected-date-row').children('div').hide();
                    td.find('.date').addClass('selected-date-row').children('div').show();
                    td.siblings('.tile-bookable').find('.date').addClass('selected-date-row').children('div').show();

                    packageData = {
                        'dateOne': date,
                        'roomType': {
                            'id': td.attr('data-room-type-id'),
                            'name': td.attr('data-room-type-name')
                        },
                        'room': {
                            'id': roomId,
                            'name': td.attr('data-room-name')
                        }
                    };
                }
            });
        },
        // get accommodation report content
        accommodationReportProcessing = false,
        accommodationReportGet = function (page) {
            var form = $('#accommodation-report-filter'),
                wrapper = $('#accommodation-report-content')
                ;

            page = typeof page !== 'undefined' ? page : 1;

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

            if (!accommodationReportProcessing) {
                var data = form.serializeObject();
                data.page = page;

                $.ajax({
                    url: Routing.generate('report_accommodation_table'),
                    data: data,
                    beforeSend: function () {
                        accommodationReportProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        $('#accommodation-report-filter-begin').val($('#accommodation-report-begin').val());
                        $('#accommodation-report-filter-end').val($('#accommodation-report-end').val());
                        accommodationReportProcessing = false;
                        $('[data-toggle="popover"]').popover({html: true});
                        choosePackages();
                        $('.accommodation-report-pagination').find('a').click(function (e) {
                            e.preventDefault();
                            accommodationReportGet($(this).text());
                        });
                    },
                    dataType: 'html'
                });
            }
        };
    if (!$('#accommodation-report-submit-button').length) {
        accommodationReportGet();
        $('.accommodation-report-filter').change(function () {
            accommodationReportGet();
        });
    }
    $('#accommodation-report-filter').on('submit', function (e) {
        e.preventDefault();
        accommodationReportGet();
    });
});

