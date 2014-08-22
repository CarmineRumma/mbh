/*global $*/
var deleteLink = function() {
    $('.delete-link').click(function(event) {
        event.preventDefault();

        var href = ($(this).attr('href')) ? $(this).attr('href') : $(this).attr('data-href');
        var action = $(this).attr('data-action');
        var link = $(this);

        $( "#entity-delete-button").unbind( "click" );

        $('#entity-delete-button').click(function(e) {
            e.preventDefault();
            if (action) {
                eval(action + '(link)');
            } else {
                window.location.href = href;
            }
        });

        if ($(this).attr('data-header')) {
            $('#entity-delete-modal-header').html($(this).attr('data-header'));
        } else {
            $('#entity-delete-modal-header').html($('#entity-delete-modal-header').attr('data-default'));
        }
        if ($(this).attr('data-text')) {
            $('#entity-delete-modal-text').html($(this).attr('data-text'));
        } else {
            $('#entity-delete-modal-text').html($('#entity-delete-modal-text').attr('data-default'));
        }
        if ($(this).attr('data-button')) {
            $('#entity-delete-button-text').html($(this).attr('data-button'));
        } else {
            $('#entity-delete-button-text').html($('#entity-delete-button-text').attr('data-default'));
        }

        if ($(this).attr('data-button-icon')) {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $(this).attr('data-button-icon'));
        } else {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $('#entity-delete-button-icon').attr('data-default'));
        }

        if ($(this).attr('data-button-class')) {
            $('#entity-delete-button').attr('class', 'btn btn-' + $(this).attr('data-button-class'));
        } else {
            $('#entity-delete-button').attr('class', 'btn btn-' + $('#entity-delete-button').attr('data-default'));
        }

        $('#entity-delete-confirmation').modal();
    });
};

var checkMessages = function() {
    $.getJSON(Routing.generate('message'), function(data) {
        var container = $('#messages');
        $('#messages').find('.message').remove();

        if (!data.length) {
            return;
        }

        $.each(data, function(index, value) {
            var autohide = (value.autohide) ? 'autohide' : '';
            $('#messages').prepend('<div class="' + autohide + ' message alert alert-' + value.type + '"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + value.text + '</div>');
        });
    });
};

/*global window */
$(document).ready(function() {
    'use strict';

    //get messages
    checkMessages();
    window.setInterval(function() {
        checkMessages();
    }, 10000);

    //Tooltips configuration
    $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"], i[data-toggle="tooltip"]').tooltip();

    //delete link
    deleteLink();

    //autohide messages
    window.setTimeout(function() {
        $(".autohide").fadeTo(400, 0).slideUp(400, function() {
            $(this).remove();
        });
    }, 5000);
});

