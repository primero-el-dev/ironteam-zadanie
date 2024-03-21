/**
 * Depends on 'messageIndexApiEndpoint' and 'messageFetchApiEndpoint'
 */

$(document).ready(function () {

    $('.table').DataTable({
        processing: true,
        serverSide: true,
        ajax: messageIndexApiEndpoint,
        columns: [
            { data: "id" },
            { data: "emailId" },
            { data: "sender" },
            { data: "receivedAt" },
            { data: "receiver" },
            { data: "content" },
            { data: null, render: data => `
                <a href="/message/${data.id}/edit" class="btn btn-mini btn-primary">Edit</a>
                <form action="/message/${data.id}/delete" class="form-inline" method="POST">
                    <button type="submit" class="btn btn-mini btn-danger">Delete</button>
                </form>`
            },
        ]
    })

    $('#fetchMessagesButton').click(function () {
        $.ajax({ url: messageFetchApiEndpoint })
            .done(function (data) {
                alert('Messages have been fetched successfully.')
            })
    })
})