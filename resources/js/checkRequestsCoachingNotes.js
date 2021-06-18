var transition = '<div style="background-color: rgba(255,255,255,.9);position:fixed;top:0;right:0;bottom:0;left:0;z-index:99999;display:none;" id="transition">';
    transition += '<div class="text-center" style="margin: 20%;position: relative;">';
    transition += '<img src="/img/processmaker_logo.png" style="filter: invert(80%);" alt="">';
    transition += '<div class="text-center">';
    transition += '<div class="spinner-border m-3 text-primary" style="width: 4.5rem; height: 4.5rem;" role="status">';
    transition += '</div>';
    transition += '</div>';
    transition += '</div>';
    transition += '</div>';
$('body').append(transition);

async function getAdminUser() {
    if (window.location.pathname == '/tasks') {
        window.location.replace("/adoa/dashboard/todo");
    }
    
    if (window.location.pathname.split('/')[1] == 'requests' && ProcessMaker.user.id != 1) {
        if (!isNaN(window.location.pathname.split('/')[2])) {
            let promise = ProcessMaker.apiClient.get('adoa/get-task/' + window.location.pathname.split('/')[2]);
            promise.then(response => {
                if (response.data.length > 0) {
                    window.location.replace('/tasks/' + response.data[0].task_id + '/edit');
                } else {
                    ProcessMaker.apiClient.get('requests/' + window.location.pathname.split('/')[2] + '?include=data').then(response => {
                        if (response.data.data.EMA_FORM_ACTION == 'DELETE' || response.data.data.FORM_ACTION == 'DELETE') {
                            window.location.replace('/adoa/dashboard/requests');
                        } else {
                            window.location.replace('/adoa/view-pdf/' + window.location.pathname.split('/')[2]);
                        }
                    });
                }
            });
        } else {
            window.location.replace('/adoa/dashboard/todo');
        }
    }

}
getAdminUser();

$(document).ready(function () {
    setTimeout(function(){
        $("li.list-group-item:contains('Due')").hide();
    }, 1000);
    $("#listRequests tr").dblclick(function(){
        if (window.location.pathname == '/adoa/dashboard/requests') {
            if ($(this).find('td').eq(8)[0].innerText == "COMPLETED") {
                ProcessMaker.alert('The request has been completed!', 'primary', '15');
            } else {
                ProcessMaker.apiClient.get('adoa/get-open-task/' + ProcessMaker.user.id + '/' + $(this).find('td').eq(0)[0].innerText).then(responseTask => {
                    if (responseTask.data.length == 0) {
                        ProcessMaker.alert('You can not open this request, because ' + $(this).find('td').eq(7)[0].innerText + ' is the owner.', 'warning', '15');
                    } else {
                        window.location = $(this).find('.fa-external-link-square-alt').parent().attr('href');
                    }
                });
            }
        } else {
            window.location = $(this).find('.fa-external-link-square-alt').parent().attr('href');
        }
    });

    $("#listRequestsAgency tr").dblclick(function(){
        if ($(this).find('td').eq(5)[0].innerText == "COMPLETED") {
            ProcessMaker.alert('The request has been completed!', 'primary', '15');
        } else {
            ProcessMaker.apiClient.get('adoa/get-open-task/' + ProcessMaker.user.id + '/' + $(this).find('td').eq(0)[0].innerText).then(responseTask => {
                if (responseTask.data.length == 0) {
                    ProcessMaker.alert('You can not open this request, because other user is the owner.', 'warning', '15');
                } else {
                    window.location = $(this).find('.fa-external-link-square-alt').parent().attr('href');
                }
            });
        }
    });

    setTimeout(function(){
        $("#transition").fadeOut("slow");
    }, 1000);

    if (window.location.pathname == '/profile/edit') {
        ProcessMaker.confirmModal('Caution', '<div class="text-left">Any changes you make in this screen will not be reflected in HRIS.<br>Do not change the email information.<div>' , () => {});
        setTimeout(function() {
            $("button:contains('Cancel')").hide();
        }, 10);
    }
});

function printPdf(request, file) {
    window.open('/adoa/view/' + request + '/' + file).print();
}

function viewPdf(request, file) {
    $('.modal-body').html('');
    $('.modal-body').html('<embed src="/adoa/view/' + request + '/' + file + '" frameborder="0" width="100%" height="800px">');
    $('#showPdf').modal('show');
}
