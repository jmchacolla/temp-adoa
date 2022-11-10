if (window.location.pathname == '/file-manager') {
    window.location.replace('/requests');
}

(function(){
    var s = document.createElement('script');
    var h = document.querySelector('head') || document.body;
    s.src = 'https://acsbapp.com/apps/app/dist/js/app.js';
    s.async = true;
    s.onload = function() {
        acsbJS.init({
            statementLink : '',
            footerHtml : '',
            hideMobile : false,
            hideTrigger : false,
            disableBgProcess : false,
            language : 'en',
            position : 'right',
            leadColor : '#146FF8',
            triggerColor : '#146FF8',
            triggerRadius : '50%',
            triggerPositionX : 'right',
            triggerPositionY : 'bottom',
            triggerIcon : 'people',
            triggerSize : 'bottom',
            triggerOffsetX : 20,
            triggerOffsetY : 20,
            mobile : {
                triggerSize : 'small',
                triggerPositionX : 'right',
                triggerPositionY : 'bottom',
                triggerOffsetX : 20,
                triggerOffsetY : 20,
                triggerRadius : '20'
            }
        });
    };
    h.appendChild(s);
})();

async function getAgencyEnabled(agency) {
    let agencyEnabled = await ProcessMaker.apiClient.get('adoa/get-agency-enabled/' + agency);
    let enabled = await agencyEnabled.data.rows[0][0];
    return enabled;
}

$('head').append('<link rel="stylesheet" type="text/css" href="/vendor/processmaker/packages/adoa/css/CssLibraryExcelBootstrapTableFilter.css">');

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

    if (window.location.pathname == '/profile/edit') {
        ProcessMaker.confirmModal('Caution', '<div class="text-left">Any changes you make in this screen will not be reflected in HRIS.<br>Do not change the email information.<div>' , () => {});
        setTimeout(function() {
            $("button:contains('Cancel')").hide();
        }, 10);
    }

    $(".btn.avatar-button.rounded-circle.overflow-hidden.p-0.m-0.d-inline-flex.border-0.btn-info").click(function() {
        setTimeout(function(){
            $("li:contains('Files')").hide();
        }, 100);
    });
});

window.printPdf = function(request, file) {
    window.open('/adoa/view/' + request + '/' + file).print();
}

window.viewPdf = function(request, file) {
    $('#showPdf .modal-body').html('');
    $('#showPdf .modal-body').html('<embed src="/adoa/view/' + request + '/' + file + '" frameborder="0" width="100%" height="800px">');
    $('#showPdf').modal('show');
}
