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

async function getAgencyEnabled(agency) {
    let agencyEnabled = await ProcessMaker.apiClient.get('adoa/get-agency-enabled/' + agency);
    let enabled = await agencyEnabled.data.rows[0][0];
    return enabled;
}

var adminGroup = 0;
var adminAgency = 0;
async function getAdminUser() {
    let responseAdmin = await ProcessMaker.apiClient.get('adoa/group-admin/' + ProcessMaker.user.id);
    let groupAdmin = await responseAdmin.data;
    if (groupAdmin.length > 0) {
        adminGroup = 1;
    }

    let responseAgency = await ProcessMaker.apiClient.get('adoa/group-admin-agency/' + ProcessMaker.user.id + '/8');
    let groupAgency = await responseAgency.data;
    if (groupAgency.length > 0) {
        adminAgency = 1;
    }

    if (window.location.pathname == '/tasks' && adminGroup == 0 && adminAgency == 0) {
        window.location.replace("/adoa/dashboard/todo");
    }

    if (window.location.pathname.split('/')[1] == 'requests' && adminGroup == 0 && adminAgency == 0) {
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

$('head').append('<link rel="stylesheet" type="text/css" href="/vendor/processmaker/packages/adoa/css/CssLibraryExcelBootstrapTableFilter.css">');

$(document).ready(function () {
    if (ProcessMaker.user.id != 1) {
        $("button#navbar-request-button").click(function () {
            $("#transition").show();
            ProcessMaker.apiClient.get('users/' + ProcessMaker.user.id).then(response => {
                getAgencyEnabled(response.data.meta.agency).then(response => {
                    if (response == 'N') {
                        $("div.card:contains('AZPerforms')").hide();
                    } else {
                        $("div.card:contains('AZPerforms')").show();
                    }
                });
            });

            let promise = ProcessMaker.apiClient.get('/requests?include=data&per_page=100&pmql=' + encodeURIComponent('((request = "AZPerforms - My Coaching Notes") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))'));
            promise.then(response => {
                let requests = response.data;
                let count = 0;
                let numRequest;
                $.each(requests.data, function (index, value) {
                    if (value.data.CON_COACHING_NOTE_TYPE === "EMPLOYEE") {
                        count++;
                        numRequest = value.id;
                    }
                });
                if (count > 0) {
                    setTimeout(function(){
                        $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').addClass("disabled");
                        ProcessMaker.alert('You can not start a Coaching Notes process for Employee until you finish your open request #' + numRequest + '.', 'primary', '15');
                        $("#transition").fadeOut("slow");
                    }, 10);
                } else {
                    setTimeout(function(){
                        $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').removeClass("disabled");
                        $("#transition").fadeOut("slow");
                    }, 10);
                }

                ////---- Hide Coaching Notes Process for Manager if the user is not a Manager
                /*let promiseUser = ProcessMaker.apiClient.get('/users/' + ProcessMaker.user.id);
                promiseUser.then(responseUser => {
                    let userInfo = responseUser.data;
                    if (userInfo.meta.manager == "N" || !userInfo) {
                        $("div.mt-3:contains('MANAGER SPACE')").hide();
                    }
                });*/
            });

            ProcessMaker.apiClient.get('/requests?include=data&pmql=' + encodeURIComponent('((request = "Remote Work - Initiate or Terminate Agreement") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))')).then(response => {
                if (response.data.data.length > 0) {
                    setTimeout(function(){
                        $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').addClass("disabled");
                        ProcessMaker.alert('You can not start a new Remote Work - Initiate or Terminate Agreement until you finish your current request.', 'primary', '15');
                        $("#transition").fadeOut("slow");
                    }, 100);
                } else {
                    setTimeout(function(){
                        $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').removeClass("disabled");
                        $("#transition").fadeOut("slow");
                    }, 100);
                }
            });

            $(".modal-body input").keyup(function(){
                $("#transition").show();
                ProcessMaker.apiClient.get('users/' + ProcessMaker.user.id).then(response => {
                    getAgencyEnabled(response.data.meta.agency).then(response => {
                        console.log(response);
                        if (response == 'N') {
                            $("div.card:contains('AZPerforms')").hide();
                        } else {
                            $("div.card:contains('AZPerforms')").show();
                        }
                    });
                });

                let promise = ProcessMaker.apiClient.get('/requests?include=data&per_page=100&pmql=' + encodeURIComponent('((request = "AZPerforms - My Coaching Notes") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))'));
                promise.then(response => {
                    let requests = response.data;
                    let count = 0;
                    let numRequest;
                    $.each(requests.data, function (index, value) {
                        if (value.data.CON_COACHING_NOTE_TYPE === "EMPLOYEE") {
                            count++;
                            numRequest = value.id;
                        }
                    });
                    if (count > 0) {
                        setTimeout(function(){
                            $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').addClass("disabled");
                            $("#transition").fadeOut("slow");
                        }, 100);
                    } else {
                        setTimeout(function(){
                            $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').removeClass("disabled");
                            $("#transition").fadeOut("slow");
                        }, 100);
                    }

                    ProcessMaker.apiClient.get('/requests?include=data&pmql=' + encodeURIComponent('((request = "Remote Work - Initiate or Terminate Agreement") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))')).then(response => {
                        if (response.data.data.length > 0) {
                            setTimeout(function(){
                                $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').addClass("disabled");
                                $("#transition").fadeOut("slow");
                            }, 100);
                        } else {
                            setTimeout(function(){
                                $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').removeClass("disabled");
                                $("#transition").fadeOut("slow");
                            }, 100);
                        }
                    });

                    ////---- Hide Coaching Notes Process for Manager if the user is not a Manager
                    /*let promiseUser = ProcessMaker.apiClient.get('/users/' + ProcessMaker.user.id);
                    promiseUser.then(responseUser => {
                        let userInfo = responseUser.data;
                        if (userInfo.meta.manager == "N" || !userInfo) {
                            $("div.mt-3:contains('MANAGER SPACE')").hide();
                        }
                    });*/
                });
            });

            setTimeout(function() {
                $(".justify-content-end.button-pagination").click(function() {
                    $("#transition").show();
                    ProcessMaker.apiClient.get('users/' + ProcessMaker.user.id).then(response => {
                        getAgencyEnabled(response.data.meta.agency).then(response => {
                            if (response == 'N') {
                                $("div.card:contains('AZPerforms')").hide();
                            } else {
                                $("div.card:contains('AZPerforms')").show();
                            }
                        });
                    });

                    let promise = ProcessMaker.apiClient.get('/requests?include=data&per_page=100&pmql=' + encodeURIComponent('((request = "AZPerforms - My Coaching Notes") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))'));
                    promise.then(response => {
                        let requests = response.data;
                        let count = 0;
                        let numRequest;
                        $.each(requests.data, function (index, value) {
                            if (value.data.CON_COACHING_NOTE_TYPE === "EMPLOYEE") {
                                count++;
                                numRequest = value.id;
                            }
                        });
                        if (count > 0) {
                            setTimeout(function(){
                                $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').addClass("disabled");
                                $("#transition").fadeOut("slow");
                            }, 100);
                        } else {
                            setTimeout(function(){
                                $("div.card-body:contains('AZPerforms - My Coaching Notes')").find('.btn-primary').removeClass("disabled");
                                $("#transition").fadeOut("slow");
                            }, 100);
                        }

                        ProcessMaker.apiClient.get('/requests?include=data&pmql=' + encodeURIComponent('((request = "Remote Work - Initiate or Terminate Agreement") AND (status = "In Progress") AND (user_id = "' + ProcessMaker.user.id + '"))')).then(response => {
                            if (response.data.data.length > 0) {
                                setTimeout(function(){
                                    $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').addClass("disabled");
                                    $("#transition").fadeOut("slow");
                                }, 100);
                            } else {
                                setTimeout(function(){
                                    $("div.card-body:contains('Remote Work - Initiate or Terminate Agreement')").find('.btn-primary').removeClass("disabled");
                                    $("#transition").fadeOut("slow");
                                }, 100);
                            }
                        });

                        ////---- Hide Coaching Notes Process for Manager if the user is not a Manager
                        /*let promiseUser = ProcessMaker.apiClient.get('/users/' + ProcessMaker.user.id);
                        promiseUser.then(responseUser => {
                            let userInfo = responseUser.data;
                            if (userInfo.meta.manager == "N" || !userInfo) {
                                $("div.mt-3:contains('MANAGER SPACE')").hide();
                            }
                        });*/
                    });
                });
            }, 1000);
        });
    }

    setTimeout(function(){
        $("li.list-group-item:contains('Due')").hide();
    }, 1000);
    if(adminGroup == 0 && adminAgency == 0) {
        $("button[title='Advanced Mode']").hide();
    }
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
    }, 1500);

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
