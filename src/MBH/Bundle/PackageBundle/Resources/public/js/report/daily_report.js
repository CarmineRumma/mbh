/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    updateDailyReportTable();
    $('#daily-report-update-table-button').click(function() {
        updateDailyReportTable();
    });
});

function updateDailyReportTable() {
    var $dailyReportWrapper = $('#daily-report');
    $dailyReportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate('packages_daily_report_table'),
        success: function(response) {
            $dailyReportWrapper.html(response);
            setScrollable();
        },
        data: {
            begin: $('#daily-report-filter-begin').val(),
            end: $('#daily-report-filter-end').val(),
            hotels: $('#daily-report-filter-hotels').val()
        }
    });
}