<script type="text/javascript" src="modules/servers/solusvmplus/js/get_user_data.js?v66"></script>
<script type="text/javascript" src="modules/servers/solusvmplus/js/rebuild.js?5"></script>
<script type="text/javascript" src="modules/servers/solusvmplus/js/hostname.js"></script>
{if $info['type'] != 'kvm'}
    <script type="text/javascript" src="modules/servers/solusvmplus/js/rootpassword.js"></script>
{/if}
<script type="text/javascript" src="modules/servers/solusvmplus/js/vncpassword.js"></script>
<link rel="stylesheet" href="modules/servers/solusvmplus/templates/assets/css/style.css?4" />
<link rel="stylesheet" href="modules/servers/solusvmplus/templates/assets/css/sweetalert.css?v66" />
<script type="text/javascript" src="modules/servers/solusvmplus/js/uilang.js"></script>
<script type="text/javascript" src="modules/servers/solusvmplus/js/sweetalert.min.js?v65"></script>

{literal}
    <script>
        $(function() {

            var reload = false;
            var url = window.location.href;
            patPre = '&serveraction=custom&a=';
            patAr = ['shutdown', 'reboot', 'boot'];
            for (var testPat in patAr) {
                pat = patPre + patAr[testPat];
                if (url.indexOf(pat) > 0) {
                    alertModuleCustomButtonSuccess = $('#alertModuleCustomButtonSuccess');
                    if (alertModuleCustomButtonSuccess) {
                        url = url.replace(pat, '');
                        window.location.href = url;
                        reload = true;
                    }
                    break;
                }
            }

            if (!reload) {
                var vserverid = {/literal}{$data.vserverid}{literal};
                window.solusvmplus_get_and_fill_client_data(vserverid);
                window.solusvmplus_hostname(vserverid, {'solusvmplus_invalidHostname': '{/literal}{$LANG.solusvmplus_invalidHostname}{literal}','solusvmplus_change':'{/literal}{$LANG.solusvmplus_change}{literal}'});
            {/literal}
            {if $info['type'] != 'kvm'}
                {literal}
                    window.solusvmplus_rootpassword(vserverid, {'solusvmplus_invalidRootpassword': '{/literal}{$LANG.solusvmplus_invalidRootpassword}{literal}','solusvmplus_change':'{/literal}{$LANG.solusvmplus_change}{literal}','solusvmplus_confirmRootPassword':'{/literal}{$LANG.solusvmplus_confirmRootPassword}{literal}','solusvmplus_confirmErrorPassword':'{/literal}{$LANG.solusvmplus_confirmErrorPassword}{literal}','solusvmplus_confirmPassword':'{/literal}{$LANG.solusvmplus_confirmPassword}{literal}'});
                {/literal}
            {/if}
            {literal}
                window.solusvmplus_vncpassword(vserverid, {'solusvmplus_invalidVNCpassword': '{/literal}{$LANG.solusvmplus_invalidVNCpassword}{literal}','solusvmplus_change':'{/literal}{$LANG.solusvmplus_change}{literal}','solusvmplus_confirmVNCPassword':'{/literal}{$LANG.solusvmplus_confirmVNCPassword}{literal}','solusvmplus_confirmErrorPassword':'{/literal}{$LANG.solusvmplus_confirmErrorPassword}{literal}','solusvmplus_confirmPassword':'{/literal}{$LANG.solusvmplus_confirmPassword}{literal}'});

                var cookieNameForAccordionGroup = 'solusvmplus_activeAccordionGroup_Client';
                var last = document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(
                        cookieNameForAccordionGroup).replace(/[\-\.\+\*]/g, "\\$&") +
                    "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")
                $("#solusvmplus_accordion .panel-collapse").removeClass('in');
                if (last == "") {
                    last = "solusvmplus_collapseSix";
                }
                if (last !== 'none') {
                    $("#" + last).addClass("in");
                }

                $("#solusvmplus_accordion").on('shown.bs.collapse', function() {
                    var active = $("#solusvmplus_accordion .in").attr('id');
                    document.cookie = cookieNameForAccordionGroup + "=" + active;
                });

                $("#solusvmplus_accordion").on('hidden.bs.collapse', function() {
                    var active = 'none';
                    document.cookie = cookieNameForAccordionGroup + "=" + active;
                });
            }
        });
    {/literal}
    {if $info['type'] == 'kvm'}
        {literal}
            var completeFlag = true;

            function KVMChangeRootPassword(userID, vserverID) {
                swal({
                        title: "{/literal}{$LANG.solusvmplus_ResetPassword}{literal}",
                        type: "info",
                        showCancelButton: true,
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true,
                        cancelButtonText: "{/literal}{$LANG.cancel}{literal}",
                        confirmButtonText: "{/literal}{$LANG.confirm}{literal}",
                    },
                    function() {
                        if (!completeFlag) {
                            return;
                        }
                        $.ajax({
                            method: "POST",
                            url: "modules/servers/solusvmplus/password.php",
                            data: {userid: userID, vserverid: vserverID},
                            dataType: 'json',
                            cache: false,
                            beforeSend: function() {
                                completeFlag = false;
                            },
                            complete: function() {
                                completeFlag = true;
                            },
                            success: function(value) {
                                if (value.status == 'success') {
                                    swal({
                                        title: "{/literal}{$LANG.solusvmplus_PasswordResetSuccess}{literal}",
                                        text: value.rootpassword,
                                        type: "success"
                                    });
                                    $('#password').html(value.rootpassword);
                                } else if (value.status == 'error') {
                                    swal({
                                        title: "{/literal}{$LANG.solusvmplus_PasswordResetError}{literal}",
                                        text: value.statusmsg,
                                        type: "error"
                                    });
                                };
                            },
                            error: function() {
                                swal("服务器忙，请稍后重试");
                            }
                        });
                    });
            }
        {/literal}
    {/if}
    {literal}
    </script>
{/literal}

<div class="row">
    <div class="col-md-12">
        <div class="meta-main">
            <div class="pull-left">
                <h4 class="head-title">{$product} | {$domain}</h4>
            </div>
            <div class="pull-right">
                <a href="javascript:location.reload()" class="btn btn-sm btn-primary"><span
                        class="fa fa-refresh"></span> {$LANG.solusvmplus_refresh}</a>
                <a href="index.php?m=ReNew&amp;id={$id}" class="btn btn-sm btn-primary"><span
                        class="zmdi zmdi-balance-wallet"></span>{$LANG.solusvmplus_ReNew}</a>
                <a href="index.php?m=transfer&amp;id={$id}" class="btn btn-sm btn-primary"><span
                        class="zmdi zmdi-balance-wallet"></span>{$LANG.solusvmplus_Transfer}</a>
                {if $showcancelbutton || $packagesupgrade}
                    {if $packagesupgrade}
                        <a href="upgrade.php?type=package&amp;id={$id}" class="btn btn-sm btn-success"><span
                                class="fa fa-arrow-up" aria-hidden="true"></span>{$LANG.upgrade}</a>
                    {/if}
                    <a href="clientarea.php?action=cancel&amp;id={$id}"
                        class="btn btn-sm btn-danger {if $pendingcancellation}disabled{/if}"><span
                            class="zmdi zmdi-delete"></span> {if
          $pendingcancellation}{$LANG.cancellationrequested}{else}{$LANG.clientareacancelrequestbutton}
                    {/if}</a>
                {/if}
            </div>
        </div>
    </div>
</div>

<div id="displayState" class="text-center">
    <div class="loading">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    {$LANG.solusvmplus_loading}
</div>

<div id="displayStateUnavailable" style="display: none">
    <strong>{$LANG.solusvmplus_unavailable}</strong>
</div>

<div class="row solusvm" style="display: none">
    <div class="col-sm-6 col-md-3">
        <div class="info-main">
            <div class="title">CPU</div>
            <div class="info">{$info['cpus']}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="info-main">
            <div class="title">
                {$LANG.solusvmplus_memory}{if $info['swap'] != '0 KB'}/SWAP{/if}
            </div>
            <div class="info">
                {$info['memory']}{if $info['swap'] != '0 KB'}<span>
                    / {$info['swap']}</span>{/if}
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="info-main">
            <div class="title">{$LANG.solusvmplus_disk}</div>
            <div class="info">{$info['disk']}</div>
        </div>
    </div>
    {if $info['mac']}
        <div class="col-sm-6 col-md-3">
            <div class="info-main">
                <div class="title">MAC</div>
                <div class="info">
                    <span>{$info['mac']}</span>
                </div>
            </div>
        </div>
    {else}
        <div class="col-sm-6 col-md-3">
            <div class="info-main">
                <div class="title">{$LANG.solusvmplus_Virtualization}</div>
                <div class="info">{$info['type']}</div>
            </div>
        </div>
    {/if}

    <div class="col-md-12 detail" style="display: none">
        <div class="row">
            <div class="col-md-7">
                <div class="left-main">
                    <ul class="list-unstyled" style="margin-bottom: 0">
                        <li>
                            {$LANG.solusvmplus_status}
                            <span>{$info['displaystatus']}</span>
                        </li>

                        <li>
                            {$LANG.solusvmplus_ipAddress}<span>{$info['connaddr']}</span>
                        </li>

                        {foreach from=$info['ipcsv'] item=extip}
                            <li>{$LANG.solusvmplus_ipAddress}<span>{$extip}</span></li>
                        {/foreach}
                        {if ($params['configoption11'] == 'Yes')}
                            {if ($params['configoption14'] == 'Yes')}
                                <li>{$LANG.solusvmplus_natPorts}<span><a
                                            href="index.php?m=SolusVMNAT&id={$serviceid}">点此打开</a></span></li>
                            {else}
                                <li>{$LANG.solusvmplus_sshPort}<span>{$info['sshport']}</span></li>
                                <li>
                                    {$LANG.solusvmplus_natPorts}<span>{$info['firstport']}-{$info['lastport']}</span>
                                </li>
                            {/if}
                        {/if}
                        <li>
                            {$LANG.solusvmplus_operatingsystem}<span class="templates">{$info['template']}</span>
                        </li>
                        <li>
                            {$LANG.solusvmplus_rootPassword}
                            <span style="color: #005588"
                                onclick="javascript:$(this).hide();$('#rootpassword').show();">[{$LANG.solusvmplus_ViewPassword}]</span>
                            <span id="rootpassword" style="display: none">{$rootpass}</span>
                        </li>
                        <li>
                            TUN/TAP
                            <span>
                                <a href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_enable}TUN/TAP？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=ontun';">[{$LANG.solusvmplus_enable}]</a>
                                <a href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_disable}TUN/TAP？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=offtun';">[{$LANG.solusvmplus_disable}]</a>
                            </span>
                        </li>
                        {if $cp['view']!='disable'}
                            <li>
                                {$LANG.solusvmplus_controlPanel}<span><a
                                        href="{$cp['url']}">{$LANG.solusvmplus_Clickbox}</a></span>
                            </li>
                            <li>{$LANG.solusvmplus_username}<span>{$cp['username']}</span></li>
                            <li>
                                {$LANG.solusvmplus_password}
                                <span style="color: #005588"
                                    onclick="javascript:$(this).hide();$('#password').show();">[{$LANG.solusvmplus_ViewPassword}]</span>
                                <span style="color: #005588"
                                    onclick="javascript:window.location.replace('clientarea.php?action=productdetails&id={$serviceid}#tabChangepw');location.reload();">[{$LANG.solusvmplus_change}]</span>
                                <span id="password" style="display: none">{$cp['password']}</span>
                            </li>
                        {/if}
                    </ul>
                </div>
            </div>
            <div class="col-md-5">
                <div class="right-main">
                    {if ($info['type'] == 'openvz')}
                        <h6>
                            {$LANG.solusvmplus_memory}:
                            <span class="pull-right">{$info['memoryused']} / {$info['memorytotal']}
                                <i>({$info['memoryfree']} {$LANG.solusvmplus_free})</i></span>
                        </h6>
                        <div class="progress active progress-lg">
                            <span class="pull-left">{$info['memoryused']}</span>
                            <span class="pull-right">{$info['memorytotal']}</span>
                            <div class="progress-bar {$info['memorycolor']} progress-bar-striped" role="progressbar"
                                aria-valuenow="{$info['memorypercent']}" aria-valuemin="0" aria-valuemax="100"
                                style="width: {$info['memorypercent']}%;"></div>
                        </div>
                        {/if} {if $info['type'] == 'openvz' || $info['type'] == 'xen'}
                        <h6>
                            {$LANG.solusvmplus_disk}:
                            <span class="pull-right">{$info['hddused']} / {$info['hddtotal']}
                                <i>({$info['hddfree']} {$LANG.solusvmplus_free})</i></span>
                        </h6>
                        <div class="progress active progress-lg">
                            <span class="pull-left">{$info['hddused']}</span>
                            <span class="pull-right">{$info['hddtotal']}</span>
                            <div class="progress-bar {$info['hddcolor']} progress-bar-striped" role="progressbar"
                                aria-valuenow="{$info['hddpercent']}" aria-valuemin="0" aria-valuemax="100"
                                style="width: {$info['hddpercent']}%;"></div>
                        </div>
                    {/if}
                    <h6>
                        {$LANG.solusvmplus_bandwidth}:
                        <span class="pull-right">{$info['bandwidthused']} / {$info['bandwidthtotal']}
                            <i>({$info['bandwidthfree']} {$LANG.solusvmplus_free})</i></span>
                    </h6>
                    <div class="progress active progress-lg">
                        <span class="pull-left">{$info['bandwidthused']}</span>
                        <span class="pull-right">{$info['bandwidthtotal']}</span>
                        <div class="progress-bar {$info['bandwidthcolor']} progress-bar-striped" role="progressbar"
                            aria-valuenow="{$info['bandwidthpercent']}" aria-valuemin="0" aria-valuemax="100"
                            style="width: {$info['bandwidthpercent']}%;"></div>
                    </div>

                    {if $params['configoption13'] != ""}
                        <div class="info-main">
                            <div class="title">提示信息</div>
                            <div class="info">{$params['configoption13']|replace:"\n":"<br />"}</div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="col-md-12">
                <div class="btm-main">
                    <ul class="list-unstyled row">
                        {if $info['displayreboot'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_reboot}？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=reboot';"
                                    class="btn btn-default"><span
                                        class="fa fa-repeat text-primary"></span>{$LANG.solusvmplus_reboot}</a>
                            </li>
                            {/if} {if $info['displayboot'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_boot}？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=boot';"
                                    class="btn btn-default"><span
                                        class="fa fa-play text-success"></span>{$LANG.solusvmplus_boot}</a>
                            </li>
                            {/if} {if $info['displayshutdown'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_shutdown}？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=shutdown';"
                                    class="btn btn-default"><span
                                        class="fa fa-stop text-danger"></span>{$LANG.solusvmplus_shutdown}</a>
                            </li>
                            {/if} {if $info['displayrebuild'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a id="rebuild" class="btn btn-default">
                                    <span class="fa fa-retweet text-info"></span>
                                    {$LANG.solusvmplus_reinstall}
                                </a>
                            </li>
                        {/if}
                        <li class="col-sm-4 col-md-2">
                            <a class="btn btn-default"
                                href="javascript:if(confirm('{$LANG.solusvmplus_Ask}{$LANG.solusvmplus_renetwork}？'))location='clientarea.php?action=productdetails&id={$serviceid}&serveraction=custom&a=renetwork';">
                                <span class="fa fa-retweet text-info"></span>
                                {$LANG.solusvmplus_renetwork}
                            </a>
                        </li>
                        {if $info['type'] == 'kvm'}
                            <li class="col-sm-4 col-md-2">
                                <a onClick="javascript:KVMChangeRootPassword({$userid}, {$data['vserverid']});"
                                    class="btn btn-default">
                                    <span class="fa fa-key text-info"></span>
                                    {$LANG.solusvmplus_reset}{$LANG.solusvmplus_password}
                                </a>
                            </li>
                            {/if} {if $info['displayconsole'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="#"
                                    onClick="window.open('modules/servers/solusvmplus/console.php?id={$serviceid}','_blank','width=670,height=400,status=no,location=no,toolbar=no,menubar=no')"
                                    class="btn btn-default"><span
                                        class="fa fa-terminal"></span>{$LANG.solusvmplus_serialConsole}</a>
                            </li>
                            {/if} {if $info['displayhtml5console'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="#"
                                    onClick="window.open('modules/servers/solusvmplus/html5console.php?id={$serviceid}','_blank','width=870,height=600,status=no,resizable=yes,copyhistory=no,location=no,toolbar=no,menubar=no,scrollbars=1')"
                                    class="btn btn-default"><span
                                        class="fa fa-terminal"></span>{$LANG.solusvmplus_html5Console}</a>
                            </li>
                            {/if} {if $info['displayvnc'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="#"
                                    onClick="window.open('modules/servers/solusvmplus/vnc.php?id={$serviceid}','_blank','width=400,height=200,status=no,location=no,toolbar=no,menubar=no')"
                                    class="btn btn-default"><span class="fa fa-terminal"></span>{$LANG.solusvmplus_vnc}</a>
                            </li>
                            {/if} {if $info['displaypanelbutton'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <a href="#" id="controlpanellink" class="btn btn-default"><span
                                        class="fa fa-terminal"></span>{$LANG.solusvmplus_controlPanel}</a>
                            </li>
                            {/if} {if $info['displayclientkeyauth'] == 1}
                            <li class="col-sm-4 col-md-2">
                                <form action="" name="solusvm" method="post">
                                    <input type="submit" class="btn btn-success" name="logintosolusvm"
                                        value="{$LANG.solusvmplus_manage}" />
                                </form>
                            </li>
                        {/if}
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="panel-group" id="solusvmplus_accordion" role="tablist" aria-multiselectable="false">
            {if $info['displaygraphs'] == 1}
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingSix">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#solusvmplus_accordion"
                                href="#solusvmplus_collapseSix" aria-expanded="false"
                                aria-controls="solusvmplus_collapseSix">
                                {$LANG.solusvmplus_graphs}
                            </a>
                        </h4>
                    </div>
                    <div id="solusvmplus_collapseSix" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="headingSix">
                        <div class="panel-body text-center">
                            {if $info['displaytrafficgraph'] == 1}
                                <img src="{$info['trafficgraphurl']}" alt="Traffic Graph Unavailable" />
                                {/if} {if $info['displayloadgraph'] == 1}
                                <img src="{$info['loadgraphurl']}" id="loadgraphurlImg" alt="Load Graph Unavailable" />
                                {/if} {if $info['displaymemorygraph'] == 1}
                                <img src="{$info['memorygraphurl']}" id="memorygraphurlImg" alt="Memory Graph Unavailable" />
                            {/if}
                        </div>
                    </div>
                </div>
                {/if} {if $info['displayrootpassword'] == 1}
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#solusvmplus_accordion"
                                href="#solusvmplus_collapseOne" aria-expanded="false"
                                aria-controls="solusvmplus_collapseOne">
                                {$LANG.solusvmplus_rootPassword}
                            </a>
                        </h4>
                    </div>
                    <div id="solusvmplus_collapseOne" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="headingOne">
                        <div class="panel-body">
                            <div class="row">
                                <div id="rootpasswordMsgSuccess" class="alert alert-success" role="alert"
                                    style="display: none"></div>
                                <div id="rootpasswordMsgError" class="alert alert-danger" role="alert"
                                    style="display: none"></div>
                            </div>
                            <div class="row margin-10">
                                <div class="col-xs-2"></div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <label for="newrootpassword">{$LANG.solusvmplus_newPassword}</label>
                                        <input type="password" class="form-control" name="newrootpassword"
                                            id="newrootpassword" placeholder="{$LANG.solusvmplus_enterRootPassword}"
                                            value="" />
                                    </div>
                                </div>
                                <div class="col-xs-2"></div>
                            </div>
                            <div class="row margin-10">
                                <div class="col-xs-2"></div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <label for="confirmnewrootpassword">{$LANG.solusvmplus_confirmPassword}</label>
                                        <input type="password" class="form-control" name="confirmnewrootpassword"
                                            id="confirmnewrootpassword"
                                            placeholder="{$LANG.solusvmplus_confirmRootPassword}" value="" />
                                    </div>
                                    <button type="button" id="changerootpassword" class="btn btn-success">
                                        {$LANG.solusvmplus_change}
                                    </button>
                                </div>
                                <div class="col-xs-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {/if} {if $info['displayhostname'] == 1}
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingThree">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#solusvmplus_accordion"
                                href="#solusvmplus_collapseThree" aria-expanded="false"
                                aria-controls="solusvmplus_collapseThree">
                                {$LANG.solusvmplus_hostname}
                            </a>
                        </h4>
                    </div>
                    <div id="solusvmplus_collapseThree" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="headingThree">
                        <div class="panel-body">
                            <div class="row">
                                <div id="hostnameMsgSuccess" class="alert alert-success" role="alert" style="display: none">
                                </div>
                                <div id="hostnameMsgError" class="alert alert-danger" role="alert" style="display: none">
                                </div>
                            </div>
                            <div class="row margin-10">
                                <div class="col-xs-2"></div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <label for="newhostname">{$LANG.solusvmplus_newHostname}</label>
                                        <input type="text" class="form-control" name="newhostname" id="newhostname"
                                            placeholder="{$LANG.solusvmplus_enterHostname}" value="" />
                                    </div>
                                    <button type="button" id="changehostname" class="btn btn-success">
                                        {$LANG.solusvmplus_change}
                                    </button>
                                </div>
                                <div class="col-xs-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {/if} {if $info['displayvncpassword'] == 1}
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingFive">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#solusvmplus_accordion"
                                href="#solusvmplus_collapseFive" aria-expanded="false"
                                aria-controls="solusvmplus_collapseFive">
                                {$LANG.solusvmplus_vncPassword}
                            </a>
                        </h4>
                    </div>
                    <div id="solusvmplus_collapseFive" class="panel-collapse collapse" role="tabpanel"
                        aria-labelledby="headingFive">
                        <div class="panel-body">
                            <div class="row">
                                <div id="vncpasswordMsgSuccess" class="alert alert-success" role="alert"
                                    style="display: none"></div>
                                <div id="vncpasswordMsgError" class="alert alert-danger" role="alert" style="display: none">
                                </div>
                            </div>
                            <div class="row margin-10">
                                <div class="col-xs-2"></div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <label for="newvncpassword">{$LANG.solusvmplus_newPassword}</label>
                                        <input type="password" class="form-control" name="newvncpassword"
                                            id="newvncpassword" placeholder="{$LANG.solusvmplus_enterVNCPassword}"
                                            value="" />
                                    </div>
                                </div>
                                <div class="col-xs-2"></div>
                            </div>
                            <div class="row margin-10">
                                <div class="col-xs-2"></div>
                                <div class="col-xs-8">
                                    <div class="form-group">
                                        <label for="confirmnewvncpassword">{$LANG.solusvmplus_confirmPassword}</label>
                                        <input type="password" class="form-control" name="confirmnewvncpassword"
                                            id="confirmnewvncpassword" placeholder="{$LANG.solusvmplus_confirmVNCPassword}"
                                            value="" />
                                    </div>
                                    <button type="button" id="changevncpassword" class="btn btn-success">
                                        {$LANG.solusvmplus_change}
                                    </button>
                                </div>
                                <div class="col-xs-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <div id="clientkeyautherror" style="display: none">
        <div class="col-md-12 bg-danger">{$LANG.solusvmplus_accessUnavailable}</div>
    </div>

    <div class="col-md-12">
        <div class="panel panel-default panel-amount">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <span class="fa fa-star"></span>{$LANG.solusvmplus_paymentDetails}
                </h3>
            </div>
            <div class="panel-body">
                <ul class="row list-unstyled top-main">
                    <li class="col-sm-6">
                        <span>{$LANG.clientareahostingregdate}</span>{$regdate}
                    </li>

                    {if $firstpaymentamount neq $recurringamount}
                        <li class="col-sm-6">
                            <span>{$LANG.firstpaymentamount}</span>{$firstpaymentamount}
                        </li>
                        {/if} {if $billingcycle != $LANG.orderpaymenttermonetime && $billingcycle != $LANG.orderfree}
                        <li class="col-sm-6">
                            <span>{$LANG.recurringamount}</span>{$recurringamount}
                        </li>
                    {/if}

                    <li class="col-sm-6">
                        <span>{$LANG.orderbillingcycle}</span>{$billingcycle}
                    </li>

                    <li class="col-sm-6">
                        <span>{$LANG.clientareahostingnextduedate}</span>{$nextduedate}
                    </li>

                    <li class="col-sm-6">
                        <span>{$LANG.orderpaymentmethod}</span>{$paymentmethod}
                    </li>

                    {if $suspendreason}
                        <li class="suspendreason col-sm-6">
                            <span>{$LANG.suspendreason}</span>{$suspendreason}
                        </li>
                    {/if}
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="rebuildmsg text-center">
    <div class="loading">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <span class="msg">{$LANG.rebuildloading}</span>
</div>

<div id="overlay">
    <form id="rebuild_form">
        <div class="rebuild-header">
            <a href="#" class="close">Close</a>
            <h1>{$LANG.solusvmplus_reinstall}</h1>
        </div>
        <div class="rebuild-content">
            <div class="row" id="templates">
                <input type="hidden" class="vserverid" name="vserverid" value="{$data['vserverid']}" />
                <input type="hidden" class="userid" name="userid" value="{$userid}" />
                {foreach $result as $key => $value}
                    <div class="{if {$key|count} <= 3}col-sm-3{else}col-sm-4{/if}">
                        <div class="template">
                            <span class="os-icon os-icon-lg os-{$key} os-linux"></span>
                            <strong class="systemname">{$key}</strong>
                            <span class="select-name" data-select="{$LANG.Selectsystem}">{$LANG.Selectsystem}</span>
                            <ul class="list-unstyled system">
                                {foreach from=$value item=os}
                                    <li data-os="{$key}" data-template="{$os['filename']}">
                                        {$os['friendlyname']}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="text-center" id="loading" style="display: none">
                <span class="text-center fa fa-spinner fa-pulse fa-5x fa-fw margin-bottom" style="margin: 50px"></span>
            </div>
        </div>

        <div class="rebuild-footer">
            <span class="pull-left" id="systemname"></span>
            <button type="submit" class="confirm" id="confirmbtn" disabled>
                {$LANG.orderForm.yes}
            </button>
        </div>
    </form>
    <div class="text-center rootpass_loading" style="display: none">
        <span class="text-center fa fa-spinner fa-pulse fa-5x fa-fw margin-bottom"
            style="margin: 20px; color: #fff"></span>
    </div>
    <code>
        clicking on "#rebuild" adds class "open" on "#overlay, #rebuild_form"
        clicking on ".close, .rebuild-footer .btn-primary" removes class "open" on
        "#overlay, #rebuild_form"
    </code>
</div>