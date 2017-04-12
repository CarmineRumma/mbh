/*global window, $, services, document, firstTableDate, packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals*/
var DATE_ELEMENT_WIDTH = 47;

//TODO: Как обрабатывать ошибки ajax?
//TODO: Мб скрывать боковое меню при переходе на вкладку шахматки?
//TODO: Если не находятся возможности бронирования, мб указывать что-то другое, а не "по вашему запросу ничего не найдено"?
//TODO: Мб переименовать размещение в шахматку?

$(document).ready(function () {
    'use strict';
    var chessBoardManager = new ChessBoardManager(packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals);
    chessBoardManager.hangHandlers();
});