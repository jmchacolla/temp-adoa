(()=>{var e,t;"/file-manager"==window.location.pathname&&window.location.replace("/requests"),e=document.createElement("script"),t=document.querySelector("head")||document.body,e.src="https://acsbapp.com/apps/app/dist/js/app.js",e.async=!0,e.onload=function(){acsbJS.init({statementLink:"",footerHtml:"",hideMobile:!1,hideTrigger:!1,disableBgProcess:!1,language:"en",position:"right",leadColor:"#146FF8",triggerColor:"#146FF8",triggerRadius:"50%",triggerPositionX:"right",triggerPositionY:"bottom",triggerIcon:"people",triggerSize:"bottom",triggerOffsetX:20,triggerOffsetY:20,mobile:{triggerSize:"small",triggerPositionX:"right",triggerPositionY:"bottom",triggerOffsetX:20,triggerOffsetY:20,triggerRadius:"20"}})},t.appendChild(e),$("head").append('<link rel="stylesheet" type="text/css" href="/vendor/processmaker/packages/adoa/css/CssLibraryExcelBootstrapTableFilter.css">'),$(document).ready((function(){setTimeout((function(){$("li.list-group-item:contains('Due')").hide()}),1e3),$("#listRequests tr").dblclick((function(){var e=this;"/adoa/dashboard/requests"==window.location.pathname?"COMPLETED"==$(this).find("td").eq(8)[0].innerText?ProcessMaker.alert("The request has been completed!","primary","15"):ProcessMaker.apiClient.get("adoa/get-open-task/"+ProcessMaker.user.id+"/"+$(this).find("td").eq(0)[0].innerText).then((function(t){0==t.data.length?ProcessMaker.alert("You do not have permission to open this request.","warning","15"):window.location=$(e).find(".fa-external-link-square-alt").parent().attr("href")})):window.location=$(this).find(".fa-external-link-square-alt").parent().attr("href")})),$("#listRequestsAgency tr").dblclick((function(){var e=this;"COMPLETED"==$(this).find("td").eq(5)[0].innerText?ProcessMaker.alert("The request has been completed!","primary","15"):ProcessMaker.apiClient.get("adoa/get-open-task/"+ProcessMaker.user.id+"/"+$(this).find("td").eq(0)[0].innerText).then((function(t){0==t.data.length?ProcessMaker.alert("You do not have permission to open this request.","warning","15"):window.location=$(e).find(".fa-external-link-square-alt").parent().attr("href")}))})),"/profile/edit"==window.location.pathname&&(ProcessMaker.confirmModal("Caution",'<div class="text-left">Any changes you make in this screen will not be reflected in HRIS.<br>Do not change the email information.<div>',(function(){})),setTimeout((function(){$("button:contains('Cancel')").hide()}),10)),$(".btn.avatar-button.rounded-circle.overflow-hidden.p-0.m-0.d-inline-flex.border-0.btn-info").click((function(){setTimeout((function(){$("li:contains('Files')").hide()}),100)}))})),window.printPdf=function(e,t){window.open("/adoa/view/"+e+"/"+t).print()},window.viewPdf=function(e,t){$("#showPdf .modal-body").html(""),$("#showPdf .modal-body").html('<embed src="/adoa/view/'+e+"/"+t+'" frameborder="0" width="100%" height="800px">'),$("#showPdf").modal("show")}})();