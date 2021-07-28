<?php
##############################################################################
###      Custom function file for SolusVM WHMCS module version 3           ###
##############################################################################
### Reaname this file to custom.php and uncomment the functions you require
### Report any bugs or feature requests for this module to the following link:
### http://developer.soluslabs.com/projects/billing-whmcs
### FULL Descriptions of these functions are located here:
### http://docs.solusvm.com/v2/Default.htm#Modules/Billing/WHMCS/Custom-Configuration.htm
##############################################################################

#function solusvmplus_customclientarea( $params, $cparams ) {
#    ###########################################################################
#    ### This function allows you to create a custom the client area.
#    ###########################################################################
#    ################################## CODE ###################################
#    global $_LANG;
#
#    $rebootbutton   = '';
#    $shutdownbutton = '';
#    $bootbutton     = '';
#    $console        = '';
#    $vnc            = '';
#    $rootpassword   = '';
#    $hostname       = '';
#    $html5console   = '';
#    $vncpassword    = '';
#    $cpbutton       = '';
#    $keylogin       = '';
#    $htmlarray      = array();
#
#    $htmlarray["clientkeyautherror"] = '';
#    $htmlarray["displaystatus"]      = '';
#    $htmlarray["bandwidthbar"]       = '';
#    $htmlarray["memorybar"]          = '';
#    $htmlarray["hddbar"]             = '';
#    $htmlarray["buttons"]            = '';
#    $htmlarray["ips"]                = '';
#    $htmlarray["graphs"]        = '';
#
#    ### Buttons ###
#    if ( $cparams["displayreboot"] ) {
#        $rebootbutton = '<button type="button" class="btn btn-default" onclick="window.location=\'clientarea.php?action=productdetails&id=' . $params["serviceid"] . '&serveraction=custom&a=reboot\'">' . $_LANG["solusvmplus_reboot"] . '</button>';
#    }
#    if ( $cparams["displayshutdown"] ) {
#        $shutdownbutton = '<button type="button" class="btn btn-default" onclick="window.location=\'clientarea.php?action=productdetails&id=' . $params["serviceid"] . '&serveraction=custom&a=shutdown\'">' . $_LANG["solusvmplus_shutdown"] . '</button>';
#    }
#    if ( $cparams["displayboot"] ) {
#        $bootbutton = '<button type="button" class="btn btn-default" onclick="window.location=\'clientarea.php?action=productdetails&id=' . $params["serviceid"] . '&serveraction=custom&a=boot\'">' . $_LANG["solusvmplus_boot"] . '</button>';
#    }
#    if ( $cparams["displayconsole"] ) {
#        $console = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/console.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=670,height=500,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_serialConsole"] . '</button>';
#    }
#    if ( $cparams["displayvnc"] ) {
#        $vnc = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/vnc.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=100,height=50,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_vnc"] . '</button>';
#    }
#    if ( $cparams["displayrootpassword"] ) {
#        $rootpassword = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/rootpassword.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=400,height=200,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_rootPassword"] . '</button>';
#    }
#    if ( $cparams["displayhostname"] ) {
#        $hostname = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/changehostname.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=400,height=200,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_hostname"] . '</button>';
#    }
#    if ( $cparams["displayhtml5console"] ) {
#        $html5console = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/html5console.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=400,height=200,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_html5Console"] . '</button>';
#    }
#    if ( $cparams["displayvncpassword"] ) {
#        $vncpassword = '<button type="button" class="btn btn-default" onclick="window.open(\'modules/servers/solusvmplus/vncpassword.php?id=' . $params["serviceid"] . '\', \'_blank\',\'width=400,height=200,status=no,location=no,toolbar=no,menubar=no\')">' . $_LANG["solusvmplus_vncPassword"] . '</button>';
#    }
#    if ( $cparams["displaypanelbutton"] ) {
#        $cpbutton = '<button type="button" class="btn btn-default" onclick="window.open(\'' . $cparams["controlpanellink"] . '\', \'_blank\')">' . $_LANG["solusvmplus_controlPanel"] . '</button>';
#    }
#    if ( $cparams["displayclientkeyauth"] ) {
#        $keylogin = '<form action="" name="solusvm" method="post"><input type="submit" class="btn-success" style="font-weight: bold;width: 135px" name="logintosolusvm" value="' . $_LANG["solusvmplus_manage"] . '"></form>';
#    }
#    $htmlarray["buttons"] = '<tr><td width="150" class="fieldarea">' . $_LANG["solusvmplus_options"] . ':&nbsp;</td><td align="left">' . $rebootbutton . '' . $shutdownbutton . '' . $bootbutton . '' . $console . '' . $vnc . '' . $rootpassword . '' . $hostname . '' . $html5console . '' . $vncpassword . '' . $cpbutton . '' . $keylogin . '</td></tr>';
#    ### Graphs ###
#    $htmlarray["graphs"] = '
#       <div class="col-md-12 margin-top-20">
#           <img id="trafficgraphurlImg" src="'.($cparams["trafficgraphurl"]).'" alt="Traffic Graph Unavailable">
#       </div>
#
#       <div class="col-md-12 margin-top-20">
#           <img id="loadgraphurlImg" src="'.($cparams["loadgraphurl"]).'" alt="Load Graph Unavailable">
#       </div>
#
#       <div class="col-md-12 margin-top-20">
#           <img id="memorygraphurlImg" src="'.($cparams["memorygraphurl"]).'" alt="Memory Graph Unavailable">
#       </div>';
#
#    ### Usage Bars ###
#    if ( $cparams["displaybandwidthbar"] ) {
#        $htmlarray["bandwidthbar"] = '
#            <div class="col-md-12 margin-top-20">
#                ' . $cparams["bandwidthused"] . ' ' . $_LANG["solusvmplus_of"] . '
#                ' . $cparams["bandwidthtotal"] . ' ' . $_LANG["solusvmplus_used"] . ' /
#                ' . $cparams["bandwidthfree"] . ' ' . $_LANG["solusvmplus_free"] . '
#            </div>
#            <div class="col-md-12">
#                <div class="progress">
#                    <div class="progress-bar" id="bandwidthProgressbar" role="progressbar"
#                         aria-valuenow="' . $cparams["bandwidthpercent"] . '" aria-valuemin="0" aria-valuemax="100"
#                         style="width: ' . $cparams["bandwidthpercent"] . '% ;min-width: 2em; background-color: ' . $cparams["bandwidthcolor"] . '">
#                        ' . $cparams["bandwidthpercent"] . '%
#                    </div>
#                </div>
#            </div>
#        ';
#    }
#    if ( $cparams["displaymemorybar"] ) {
#        $htmlarray["memorybar"] = '
#            <div class="col-md-12 margin-top-20">
#                ' . $cparams["memoryused"] . ' ' . $_LANG["solusvmplus_of"] . '
#                ' . $cparams["memorytotal"] . ' ' . $_LANG["solusvmplus_used"] . ' /
#                ' . $cparams["memoryfree"] . ' ' . $_LANG["solusvmplus_free"] . '
#            </div>
#            <div class="col-md-12">
#                <div class="progress">
#                    <div class="progress-bar" id="memoryProgressbar" role="progressbar"
#                         aria-valuenow="' . $cparams["memorypercent"] . '" aria-valuemin="0" aria-valuemax="100"
#                         style="width: ' . $cparams["memorypercent"] . '% ;min-width: 2em; background-color: ' . $cparams["memorycolor"] . '">
#                        ' . $cparams["memorypercent"] . '%
#                    </div>
#                </div>
#            </div>
#        ';
#    }
#    if ( $cparams["displayhddbar"] ) {
#        $htmlarray["hddbar"] = '
#            <div class="col-md-12 margin-top-20">
#                ' . $cparams["hddused"] . ' ' . $_LANG["solusvmplus_of"] . '
#                ' . $cparams["hddtotal"] . ' ' . $_LANG["solusvmplus_used"] . ' /
#                ' . $cparams["hddfree"] . ' ' . $_LANG["solusvmplus_free"] . '
#            </div>
#            <div class="col-md-12">
#                <div class="progress">
#                    <div class="progress-bar" id="hddProgressbar" role="progressbar"
#                         aria-valuenow="' . $cparams["hddpercent"] . '" aria-valuemin="0" aria-valuemax="100"
#                         style="width: ' . $cparams["hddpercent"] . '% ;min-width: 2em; background-color: ' . $cparams["hddcolor"] . '">
#                        ' . $cparams["hddpercent"] . '%
#                    </div>
#                </div>
#            </div>
#        ';
#    }
#    if ( $cparams["clientkeyautherror"] ) {
#        $htmlarray["clientkeyautherror"] = '<div class="alert alert-block alert-error"><p>' . $_LANG["solusvmplus_accessUnavailable"] . '</p></div>';
#    }
#    ### Misc ###
#    if ( $cparams["displaystatus"] ) {
#        $htmlarray["displaystatus"] = '
#            <div class="col-md-3">
#                ' . $_LANG["solusvmplus_status"] . '
#            </div>
#            <div class="col-md-9" id="displayState">
#                ' . $cparams["displaystatus"] . '
#            </div>
#        ';
#    }
#    if ( $cparams["displayips"] ) {
#        $htmlarray["ips"] = '
#            <div class="col-md-3 margin-top-20">
#                ' . $_LANG["solusvmplus_ipAddress"] . '
#            </div>
#            <div class="col-md-9 margin-top-20">
#                ' . $cparams["ipcsv"] . '
#            </div>
#        ';
#    }
#    $output = '
#        <style>
#            .margin-top-20 {
#                margin-top: 20px;
#            }
#            .margin-5-button button {
#                margin-top: 5px;
#                margin-bottom: 5px;
#            }
#        </style>
#        <div class="row">
#            ' . $htmlarray["clientkeyautherror"] . '
#
#            ' . $htmlarray["displaystatus"] . '
#            ' . $htmlarray["bandwidthbar"] . '
#            ' . $htmlarray["memorybar"] . '
#            ' . $htmlarray["hddbar"] . '
#            <div class="col-md-12 margin-5-button">
#                ' . $htmlarray["buttons"] . '
#            </div>
#		    ' . $htmlarray["graphs"] . '
#		    ' . $htmlarray["ips"] . '
#        </div>
#    ';
#
#    return $output;
#}

# function solusvmplus_hostname($params)
# {
#   ###########################################################################
#   ### This function allows you to randomly create a hostname when a virtual
#   ### server is ordered, if no hostname is specified.
#   ###########################################################################
#   ################################## CODE ###################################
#   $serviceid = $params["serviceid"];
#   $clientsdetails = $params["clientsdetails"];
#   if(!empty($params[domain])) {
#     $currentHost = $params[domain] {
#       strlen($params[domain]) - 1}
#     ;
#     if(!strcmp($currentHost, ".")) {
#       $newHost = substr($params[domain], 0, -1);
#       mysql_real_escape_string($newHost);
#       mysql_query("UPDATE tblhosting SET `domain` = '$newHost' WHERE `id` = '$serviceid'");
#     } else {
#       $newHost = $params[domain];
#     }
#   } else {
#     $newHost = "vps" . $serviceid . $clientsdetails['id'] . ".EXAMPLEDOMAIN.COM";
#   }
#   return $newHost;
# }

# function solusvmplus_username($params)
# {
#   ###########################################################################
#   ### This function allows you to create a random username for the solusvm
#   ### login. This is handy if you want seperate accounts for all virtual
#   ### servers.
#   ###########################################################################
#   ################################## CODE ###################################
#   $uniqueCod = md5(uniqid(mt_rand(), true));
#   $uniqueCod = substr($uniqueCod, 1, 5);
#   $uname = "USER" . $serviceid . $uniqueCod;
#   return $uname;
# }

# function solusvmplus_AdminLink($params)
# {
#   ###########################################################################
#   ### This function allows you to create a direct login link to your admincp
#   ### from the server list in whmcs
#   ###########################################################################
#   ################################## CODE ###################################
#   $code = '<form action="https://' . $params["serverip"] . ':5656/admincp/login.php" method="post" target="_blank">
#             <input type="hidden" name="username" value="ADMINUSERNAME" />
#             <input type="hidden" name="password" value="ADMINPASSOWRD" />
#             <input type="submit" name="Submit" value="Login" />
#             </form>
#			';
#   return $code;
# }

# function solusvmplus_create_one($params)
# {
# }

# function solusvmplus_create_two($params)
# {
# }

# function solusvmplus_create_three($params)
# {
# }

# function solusvmplus_create_four($params)
# {
# }

# function solusvmplus_create_five($params)
# {
# }

# function solusvmplus_terminate_pre($params)
# {
# }

# function solusvmplus_terminate_post_success($params)
# {
# }

# function solusvmplus_terminate_post_error($params)
# {
# }

# function solusvmplus_suspend_pre($params)
# {
# }

# function solusvmplus_suspend_post_success($params)
# {
# }

# function solusvmplus_suspend_post_error($params)
# {
# }

# function solusvmplus_unsuspend_pre($params)
# {
# }

# function solusvmplus_unsuspend_post_success($params)
# {
# }

# function solusvmplus_unsuspend_post_error($params)
# {
# }

# function solusvmplus_changepackage_pre($params)
# {
# }

# function solusvmplus_changepackage_post_success($params)
# {
# }

# function solusvmplus_changepackage_post_error($params)
# {
# }

