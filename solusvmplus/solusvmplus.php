<?php
require_once __DIR__ . '/lib/Curl.php';
require_once __DIR__ . '/lib/CaseInsensitiveArray.php';
require_once __DIR__ . '/lib/SolusVM.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use SolusVMPlus\SolusVM as SolusVM;

if ( ! defined( "WHMCS" ) ) {
    die( "This file cannot be accessed directly" );
}

if ( file_exists( __DIR__ . "/custom.php" ) ) {
    require_once( __DIR__ . "/custom.php" );
}

SolusVM::loadLang();

function solusvmplus_initConfigOption()
{
        $sql = Capsule::table('tblproducts')->where('servertype', 'solusvmplus')->where('id', $_REQUEST['id']);
        
    $packageconfigoption = [];
    if($sql->exists()) {
        $data = $sql->first();
        $packageconfigoption[1] = $data->configoption1;
        $packageconfigoption[3] = $data->configoption3;
        $packageconfigoption[5] = $data->configoption5;
    }

    return $packageconfigoption;
}

function solusvmplus_ConfigOptions() {
	try {
	    $packageconfigoption = solusvmplus_initConfigOption();
	    
        $master_array = array();
        /** @var stdClass $row */
        foreach ( Capsule::table( 'tblservers' )->where( 'type', 'solusvmplus' )->get() as $row ) {
            $master_array[] = $row->id . " - " . $row->name;
        }

        $master_list = implode( ",", $master_array );

        $vt = '';
        if ( $packageconfigoption[5] == "OpenVZ" ) {
            $vt = "openvz";
        } elseif ( $packageconfigoption[5] == "Xen-PV" ) {
            $vt = "xen";
        } elseif ( $packageconfigoption[5] == "Xen-HVM" ) {
            $vt = "xen hvm";
        } elseif ( $packageconfigoption[5] == "KVM" ) {
            $vt = "kvm";
        }

        $solusvm = new SolusVM( [ 'configoption1' => $packageconfigoption[1], 'configoption3' => $packageconfigoption[3] ] );

        $callArray = array( "type" => $vt );

        ## List plans
        $solusvm->apiCall( 'listplans', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $default_plan = $solusvm->result["plans"];
        } else {
            $default_plan = $solusvm->rawResult;
        }

        ## List nodes
        $solusvm->apiCall( 'listnodes', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $default_node = $solusvm->result["nodes"];
        } else {
            $default_node = $solusvm->rawResult;
        }

        ## List node groups
        $solusvm->apiCall( 'listnodegroups', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $default_nodegroup = $solusvm->result["nodegroups"];
        } else {
            $default_nodegroup = $solusvm->rawResult;
        }

        ## List templates
        $solusvm->apiCall( 'listtemplates', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
	        if ( $vt == "kvm" ) {
            	$default_template = $solusvm->result["templateskvm"];
	        } else {
            	$default_template = $solusvm->result["templates"];
	        }
        } else {
            $default_template = $solusvm->rawResult;
        }

        $iprange     = implode( ",", range( 1, 500 ) );
	
 $configarray = array(
            "用户类型"         => array( "Type" => "dropdown", "Options" => "Admin,Reseller",),
            "节点"             => array( "Type" => "dropdown", "Options" => "$default_node", ),
            "服务器"            => array( "Type" => "dropdown", "Options" => "$master_list",  ),
            "套餐"             => array( "Type" => "dropdown", "Options" => "$default_plan", ),
            "虚拟化类型"      => array( "Type" => "dropdown", "Options" => "OpenVZ,Xen-PV,Xen-HVM,KVM", ),
            "默认系统" => array( "Type" => "dropdown", "Options" => "$default_template", ),
            "用户名前缀"          => array( "Type" => "text", "Size" => "25", ),
            "IP地址数量"             => array( "Type" => "dropdown", "Options" => "$iprange", ),
            "节点组"               => array( "Type" => "dropdown", "Options" => "$default_nodegroup", ),
            "内部IP"              => array( "Type" => "dropdown", "Options" => "No,Yes", ),
            "NAT VPS"              => array( "Type" => "dropdown", "Options" => "No,Yes", ),
            "初始流量"             => array("Type" => "text" ,"Description" => "G"),
            "提示信息"             => array("Type" => "textarea" ,"Description" => "在客户区面板显示"),
            "自选端口"             => array( "Type" => "dropdown", "Options" => "No,Yes", "Description" => '需要配合SolusVMNAT插件使用'),
            "TUN/TAP"             => array( "Type" => "dropdown", "Options" => "No,Yes", "Description" => 'TUN/TAP用户区开关'),
        );

		return $configarray;
        
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Config Options',
            __FUNCTION__,
            '',
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}


function solusvmplus_CreateAccount( $params ) {
  
    try {
        $solusvm = new SolusVM( $params );

        $serviceid      = $solusvm->getParam( "serviceid" ); # Unique ID of the product/service in the WHMCS Database
        $clientsdetails = $solusvm->getParam( "clientsdetails" );

        # Product module option settings from ConfigOptions array above
        $configOptionPlan       = $solusvm->getParam( "configoption4" );
        $configOptionIpCount    = $solusvm->getParam( "configoption8" );
        $configOptionInternalIp = $solusvm->getParam( "configoption10" );
        # Array of clients details - firstname, lastname, email, country, etc...
        $customField = $solusvm->getParam( "customfields" ); # Array of custom field values for the product

        if ( function_exists( 'solusvmplus_create_one' ) ) {
            solusvmplus_create_one( $params );
        }

        $newDataPassword = $solusvm->getNewDataPassword();
        if ( function_exists( 'solusvmplus_username' ) ) {
            $clientUsername = solusvmplus_username( $params );
        } else {
            $clientUsername = $solusvm->getUsername();
        }
        $buildNode            = $solusvm->getBuildNode();
        $buildOperatingSystem = $solusvm->getBuildOperatingSystem();
        $cpGroup              = $solusvm->getCPGroup();
        $buildGroup           = $solusvm->getBuildGroup();

        #########################################
        ## Custom settings from config options ##
        #########################################

        $cmem       = $solusvm->getCmem();
        $cdisk      = $solusvm->getCdisk();
        $cbandwidth = $solusvm->getCbandwidth();
        $ccpu       = $solusvm->getCcpu();
        $cextraip   = $solusvm->getCextraip();
        $cnspeed    = $solusvm->getCnspeed();

        #########################################

        if ( function_exists( 'solusvmplus_hostname' ) ) {
            $newHost = solusvmplus_hostname( $params );
        } else {
            $newHost = $solusvm->getHostname();
        }

        if ( function_exists( 'solusvmplus_create_two' ) ) {
            solusvmplus_create_two( $params );
        }

        ## The call string for the connection function
        $callArray = array(
            "username"  => $clientUsername,
            "password"  => $newDataPassword,
            "email"     => $clientsdetails['email'],
            "firstname" => $clientsdetails['firstname'],
            "lastname"  => $clientsdetails['lastname']
        );

        $solusvm->apiCall( 'client-create', $callArray );
        $r = $solusvm->result;

        if ( $r["status"] != "success" && $r["statusmsg"] != "Client already exists" ) {
            return "Cannot create client";
        }

        ## Update the username field
        Capsule::table( 'tblhosting' )
               ->where( 'id', $serviceid )
               ->update(
                   [
                       'username' => $clientUsername,
                   ]
               );

        $returnData["password"] = $r["password"];

        ## Check for a vserverid and if it exists check to see if theres already a vps created
        $noVps = '';
        $r     = array();
        if ( empty( $customField['vserverid'] ) ) {
            $noVps = 'success';
        } else {
            ## The call string for the connection fuction
            $callArray = array( "vserverid" => $customField['vserverid'] );
            $solusvm->apiCall( 'vserver-checkexists', $callArray );
            $r = $solusvm->result;
        }

        if ( $r["statusmsg"] != "Virtual server not found" && $noVps != 'success' ) {
            return "Virtual server already exists";
        }

        ## Rename the types so the API can read them
        $vt = $solusvm->getVT();

        if ( function_exists( 'solusvmplus_create_three' ) ) {
            solusvmplus_create_three( $params );
        }

        if ( $configOptionInternalIp == "Yes" ) {
            $isinternalip = "1";
        } else {
            $isinternalip = "0";
        }
        ## The call string for the connection function
        $callArray = array(
            "customextraip"   => $cextraip,
            "customcpu"       => $ccpu,
            "custombandwidth" => $cbandwidth,
            "customdiskspace" => $cdisk,
            "custommemory"    => $cmem,
            "customnspeed"    => $cnspeed,
            "hvmt"            => "1",
            "type"            => $vt,
            "nodegroup"       => $buildGroup,
            "node"            => $buildNode,
            "hostname"        => $newHost,
            "username"        => $clientUsername,
            "password"        => $newDataPassword,
            "plan"            => $configOptionPlan,
            "template"        => $buildOperatingSystem,
            "issuelicense"    => $cpGroup,
            "internalip"      => $isinternalip,
            "ips"             => $configOptionIpCount
        );

        logModuleCall(
            'create account',
            '',
            $callArray,
            $callArray,
            ''
        );


        $solusvm->apiCall( 'vserver-create', $callArray );
        $r = $solusvm->result;

        if ( $r["status"] == "success" ) {

            $fields = [
                "vserverid",
                "nodeid",
                "consoleuser",
                "rootpassword",
                "instructions",
                "vncip",
                "vncport",
                "vncpassword",
                "internalip"
            ];
            
            foreach ( $fields as $field ) {
                if ( ! isset( $r[ $field ] ) ) {
                    $r[ $field ] = '';
                }
            }

            foreach ( $fields as $field ) {
                $solusvm->setCustomfieldsValue( $field, $r[ $field ] );
            }
      if(floor((int)$params['configoption12']) + (int)$params['configoptions']['Extra Bandwidth'] > 0){
          $limit = (string)floor((int)$params['configoption12']) + (int)$params['configoptions']['Extra Bandwidth'];
        $callArray = array("vserverid" => $r['vserverid'],"limit"=> $limit ,"overlimit" =>  '0');
        $solusvm->apiCall( 'vserver-bandwidth', $callArray );
      }

            ## Insert the dedicated ip
            $mainip = $r["mainipaddress"];
            Capsule::table( 'tblhosting' )
                   ->where( 'id', $serviceid )
                   ->update(
                       [
                           'dedicatedip' => $mainip,
                       ]
                   );

            ## Update the hostname just in case solus changed it
            $solusvm->setHostname( $r["hostname"] );

            ## Sort out the extra ip's if there is any
            $extraip = $r["extraipaddress"];
            if ( ! empty( $extraip ) ) {
                ## Remove the comma and replace with a line break
                $iplist = str_replace( ",", "\n", $extraip );
                Capsule::table( 'tblhosting' )
                       ->where( 'id', $serviceid )
                       ->update(
                           [
                               'assignedips' => $iplist,
                           ]
                       );
            }
            $result = "success";

        } else { // else creation failed
            $result = $r["statusmsg"];
        }

        if ( $result == "success" ) {
            if ( function_exists( 'solusvmplus_create_four' ) ) {
                solusvmplus_create_four( $params );
            }
        } else {
            if ( function_exists( 'solusvmplus_create_five' ) ) {
                solusvmplus_create_five( $params );
            }
        }

        return $result;

    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Create Account',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

function solusvmplus_SuspendAccount( $params ) {
    try {
        if ( function_exists( 'solusvmplus_suspend_pre' ) ) {
            solusvmplus_suspend_pre( $params );
        }

        $solusvm = new SolusVM( $params );

        $customField = $solusvm->getParam( "customfields" );

        ## The call string for the connection fuction
        $callArray = array( "vserverid" => $customField["vserverid"] );

        $solusvm->apiCall( 'vserver-suspend', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        if ( $result == "success" ) {
            if ( function_exists( 'solusvmplus_suspend_post_success' ) ) {
                solusvmplus_suspend_post_success( $params );
            }
        } else {
            if ( function_exists( 'solusvmplus_suspend_post_error' ) ) {
                solusvmplus_suspend_post_error( $params );
            }
        }

        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Suspend Account',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

function solusvmplus_UnsuspendAccount( $params ) {
    try {
        if ( function_exists( 'solusvmplus_unsuspend_pre' ) ) {
            solusvmplus_unsuspend_pre( $params );
        }

        $solusvm = new SolusVM( $params );

        $customField = $solusvm->getParam( "customfields" );

        ## The call string for the connection fuction
        $callArray = array( "vserverid" => $customField["vserverid"] );

        $solusvm->apiCall( 'vserver-unsuspend', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        if ( $result == "success" ) {
            if ( function_exists( 'solusvmplus_unsuspend_post_success' ) ) {
                solusvmplus_unsuspend_post_success( $params );
            }
        } else {
            if ( function_exists( 'solusvmplus_unsuspend_post_error' ) ) {
                solusvmplus_unsuspend_post_error( $params );
            }
        }

        return $result;

    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Unsuspend Account',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

function solusvmplus_TerminateAccount( $params ) {
    try {
        if ( function_exists( 'solusvmplus_terminate_pre' ) ) {
            solusvmplus_terminate_pre( $params );
        }

        $solusvm = new SolusVM( $params );

        $customField = $solusvm->getParam( "customfields" );
        $callArray   = array(
            "vserverid"    => $customField["vserverid"],
            "deleteclient" => "true",
        );

        $solusvm->apiCall( 'vserver-terminate', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $solusvm->removeipTerminatedProduct();

            $solusvm->removevserveridTerminatedProduct();

            $solusvm->setCustomfieldsValue( 'nodeid', "" );
            $solusvm->setCustomfieldsValue( 'rootpassword', "" );
            $solusvm->setCustomfieldsValue( 'instructions', "" );
            $solusvm->setCustomfieldsValue( 'vncip', "" );
            $solusvm->setCustomfieldsValue( 'vncport', "" );
            $solusvm->setCustomfieldsValue( 'vncpassword', "" );
            $solusvm->setCustomfieldsValue( 'internalip', "" );

            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        if ( $result == "success" ) {
            if ( function_exists( 'solusvmplus_terminate_post_success' ) ) {
                solusvmplus_terminate_post_success( $params );
            }
        } else {
            if ( function_exists( 'solusvmplus_terminate_post_error' ) ) {
                solusvmplus_terminate_post_error( $params );
            }
        }

        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Terminate Account',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}


function solusvmplus_AdminCustomButtonArray() {
    global $_LANG;

    return array(
        $_LANG["solusvmplus_reboot"]   => "reboot",
        $_LANG["solusvmplus_shutdown"] => "shutdown",
        $_LANG["solusvmplus_boot"]     => "boot",
        $_LANG["solusvmplus_enable"].' TUN/TAP' => "ontun",
        $_LANG["solusvmplus_disable"].' TUN/TAP' => "offtun",
        $_LANG["solusvmplus_renetwork"] => "renetwork",
    );
}

function solusvmplus_ClientAreaCustomButtonArray() {
    global $_LANG;

    return array(
        $_LANG["solusvmplus_reboot"]   => "reboot",
        $_LANG["solusvmplus_shutdown"] => "shutdown",
        $_LANG["solusvmplus_boot"]     => "boot",
        $_LANG["solusvmplus_enable"].' TUN/TAP' => "ontun",
        $_LANG["solusvmplus_disable"].' TUN/TAP' => "offtun",
        $_LANG["solusvmplus_renetwork"] => "renetwork",
    );
}

################################################################################
### Reboot function                                                          ###
################################################################################

function solusvmplus_reboot( $params ) {
    try {
        $solusvm     = new SolusVM( $params );
        $customField = $solusvm->getParam( "customfields" );

        ## The call string for the connection fuction
        $callArray = array( "vserverid" => $customField["vserverid"] );

        $solusvm->apiCall( 'vserver-reboot', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'reboot',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

################################################################################
### Boot function                                                            ###
################################################################################

function solusvmplus_boot( $params ) {
    try {
        $solusvm     = new SolusVM( $params );
        $customField = $solusvm->getParam( "customfields" );

        ## The call string for the connection fuction
        $callArray = array( "vserverid" => $customField["vserverid"] );

        $solusvm->apiCall( 'vserver-boot', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'boot',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

################################################################################
### Shutdown function                                                        ###
################################################################################

function solusvmplus_shutdown( $params ) {
    try {
        $solusvm     = new SolusVM( $params );
        $customField = $solusvm->getParam( "customfields" );

        ## The call string for the connection fuction
        $callArray = array( "vserverid" => $customField["vserverid"] );

        $solusvm->apiCall( 'vserver-shutdown', $callArray );

        if ( $solusvm->result["status"] == "success" ) {
            $result = "success";
        } else {
            $result = (string) $solusvm->result["statusmsg"];
        }

        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'shutdown',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}


################################################################################
### Upgrade / Downgrade account function                                     ###
################################################################################
function solusvmplus_ChangePackage( $params ) {
    global $_LANG;
    try {
        if ( function_exists( 'solusvmplus_changepackage_pre' ) ) {
            $res = solusvmplus_changepackage_pre( $params );
            if( $res['cancel_process'] === true ){
                return $_LANG['solusvmplus_cancel_custom_package_change_process'];
            }
        }
        $solusvm     = new SolusVM( $params );
        $customField = $solusvm->getParam( "customfields" );
        #########################################
        ## Custom settings from config options ##
        #########################################
        $cmem       = $solusvm->getCmem();
        $cdisk      = $solusvm->getCdisk();
        $ccpu       = $solusvm->getCcpu();
        $cextraip   = $solusvm->getCextraip();
        $cnspeed    = $solusvm->getCnspeed();
        #########################################
        //Apply custom resources
        if ( !empty($cmem) || !empty($cdisk) || !empty($ccpu) || !empty($cextraip) ){
            $resource_errors = "";
            $error_divider = " ";
            if ( strpos($cmem, ':') !== false ){
                $cmem = str_replace(":", "|", $cmem);
                $solusvm->apiCall( 'vserver-change-memory', array( "memory" => $cmem, "vserverid" => $customField["vserverid"] ) );
                if ( $solusvm->result["status"] != "success" ) {
                    $resource_errors = (string) $solusvm->result["statusmsg"] . $error_divider;
                }
            }
            if ( $cdisk > 0 ){
                $solusvm->apiCall( 'vserver-change-hdd', array( "hdd" => $cdisk, "vserverid" => $customField["vserverid"] ) );
                if ( $solusvm->result["status"] != "success" ) {
                    $resource_errors .= (string) $solusvm->result["statusmsg"] . $error_divider;
                }
            }
            if ( $ccpu > 0 ){
                $solusvm->apiCall( 'vserver-change-cpu', array( "cpu" => $ccpu, "vserverid" => $customField["vserverid"] ) );
                if ( $solusvm->result["status"] != "success" ) {
                    $resource_errors .= (string) $solusvm->result["statusmsg"];
                }
            }
            if ( $cnspeed >= 0 ){
                $solusvm->apiCall( 'vserver-change-nspeed', array( "customnspeed" => $cnspeed, "vserverid" => $customField["vserverid"] ) );
                if ( $solusvm->result["status"] != "success" ) {
                    $resource_errors .= (string) $solusvm->result["statusmsg"];
                }
            }
            if ( $cextraip > 0 ){
                //first() function doesn't work
                $ipaddresses = Capsule::table('tblhosting')->select('assignedips')->where( 'id', $params['serviceid'] )->get();
                $ips = $ipaddresses[0]->assignedips;
                $lines_arr = explode(PHP_EOL, $ips);
                $num_current_ips = count($lines_arr);
                if( empty($lines_arr[0]) ){
                    $num_current_ips -= 1;
                }
                $additional_ips_needed = $cextraip - $num_current_ips;
                if ( $additional_ips_needed > 0 ){
                    for($i=1; $i<=$additional_ips_needed;$i++){
                        $solusvm->apiCall( 'vserver-addip', array( "vserverid" => $customField["vserverid"] ) );
                        if ( $solusvm->result["status"] != "success" ) {
                            $resource_errors .= (string) $solusvm->result["statusmsg"] . $error_divider;
                            break;
                        } else {
                            $lines_arr[] = $solusvm->result['ipaddress'];
                        }
                    }
                } else {
                    for($i=0; $i>$additional_ips_needed;$i--){
                        $solusvm->apiCall( 'vserver-delip', array( "vserverid" => $customField["vserverid"], "ipaddr" => $lines_arr[0]) );
                        if ( $solusvm->result["status"] != "success" ) {
                            $resource_errors .= (string) $solusvm->result["statusmsg"] . $error_divider;
                            break;
                        } else {
                            array_splice($lines_arr,0, 1);
                        }
                    }
                }
            }
            $ipArr = implode(PHP_EOL, $lines_arr);
            if(!empty($ipArr)){
                Capsule::table('tblhosting')->where( 'id', $params['serviceid'] )->update(['assignedips' => $ipArr]);
            }
            $result = empty( $resource_errors )? "success" : $resource_errors;
        } else { // full plan change
            ## The call string for the connection function
            $callArray = array(
                "plan"            => $params["configoption4"],
                "type"            => $solusvm->getVT(),
                "vserverid"       => $customField["vserverid"]
            );
        
            $solusvm->apiCall( 'vserver-change', $callArray );
            
            if((int)$params['configoptions']['Extra Bandwidth'] > 0){
            $limit = (string)floor((int)$params['configoption12']) + (int)$params['configoptions']['Extra Bandwidth'];
            $callArray = array("vserverid" => $customField['vserverid'],"limit"=> $limit ,"overlimit" =>  '0');
            $solusvm->apiCall( 'vserver-bandwidth', $callArray );
            }
            solusvmplus_UnsuspendAccount($params);
           
            if ( $solusvm->result["status"] == "success" ) {
                $result = "success";
            } else {
                $result = (string) $solusvm->result["statusmsg"];
            }
        }
        if ( $result == "success" ) {
            if ( function_exists( 'solusvmplus_changepackage_post_success' ) ) {
                solusvmplus_changepackage_post_success( $params );
            }
        } else {
            if ( function_exists( 'solusvmplus_changepackage_post_error' ) ) {
                solusvmplus_changepackage_post_error( $params );
            }
        }
        return $result;
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Change Package',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }
}

function solusvmplus_AdminServicesTabFields( $params ) {
    global $_LANG;
    try {
        $solusvm     = new SolusVM( $params );
        $serviceid   = $solusvm->getParam( 'serviceid' );
        $serverid    = $solusvm->getParam( "serverid" );
        $customField = $solusvm->getParam( "customfields" );

        $vserverid = '';
        if ( isset( $customField["vserverid"] ) ) {
            $vserverid = $customField["vserverid"];
        }

        if ( $solusvm->getExtData( "admin-control" ) == "disable" ) {
            return array();
        } else {



            $fieldTitle = $_LANG['solusvmplus_control'] . ' <button type="button" name="live" id="live" class="btn btn-sm" onclick="loadcontrol();" value="' . $_LANG['solusvmplus_refresh'] . '">' . $_LANG['solusvmplus_refresh'] . '</button>';

            $userid = 0;
            if(isset($_GET['userid'])){
                $userid = (int)$_GET['userid'];
            }

            $fieldBodyOnLoad   = '<script type="text/javascript">function loadcontrol(){var control = $(\'#control\'); control.html(\'<p>loading...</p>\'); control.load("../modules/servers/solusvmplus/svm_control.php?userid='.$userid.'&id=' . $vserverid . '&serverid=' . $serverid . '&serviceid=' . $serviceid . '");}$(document).ready(function(){loadcontrol()});</script><div id="control"></div>';
            $fieldBodyOnDemand = '<script type="text/javascript">function loadcontrol(){var control = $(\'#control\'); control.html(\'<p>loading...</p>\'); control.load("../modules/servers/solusvmplus/svm_control.php?userid='.$userid.'&id=' . $vserverid . '&serverid=' . $serverid . '&serviceid=' . $serviceid . '");}</script><div id="control"></div>';

    $nat_ports = <<<HTML
    <a class="btn btn-default" onClick="window.open('addonmodules.php?module=SolusVMNAT&page=rules&sid={$params['serviceid']}','target','');">管理面板</a>
HTML;
            if ( $solusvm->getExtData( "admin-control-type" ) == "onload" ) {
                $fieldsarray = array( $fieldTitle => $fieldBodyOnLoad );
            } elseif ( $solusvm->getExtData( "admin-control-type" ) == "ondemand" ) {
                $fieldsarray = array( $fieldTitle => $fieldBodyOnDemand );
            } else {
                $fieldsarray = array( $fieldTitle => $fieldBodyOnLoad );
            }

            if ( $params['configoption14'] == 'Yes' ){
                $fieldsarray['NAT端口'] = $nat_ports;
            }
            return $fieldsarray;
        }
    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Admin Services Tab Fields',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

if ( ! function_exists( 'solusvmplus_AdminLink' ) ) {
    function solusvmplus_AdminLink( $params ) {
        try {
            $solusvm = new SolusVM( $params );

            $fwdurl = $solusvm->apiCall( 'fwdurl' );

            $code = '<form action="' . ( $fwdurl ) . '/admincp/login.php" method="post" target="_blank">
                <input type="hidden" name="username" value="ADMINUSERNAME" />
                <input type="hidden" name="password" value="ADMINPASSOWRD" />
                <input type="submit" name="Submit" value="Login" />
                </form>';

            return $code;

        } catch ( Exception $e ) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'Admin Link',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            return $e->getMessage();
        }
    }
}

function solusvmplus_Custom_ChangeHostname( $params = '' ) {
    global $_LANG;

    $newhostname   = $_GET['newhostname'];
    $check_section = SolusVM::dns_verify_rdns_section( $newhostname );
    if ( $check_section ) {
        ## The call string for the connection function

        $callArray = array( "vserverid" => $_GET['vserverid'], "hostname" => $newhostname );

        $solusvm = new SolusVM( $params );

        if ( $solusvm->getExtData( "clientfunctions" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }
        if ( $solusvm->getExtData( "hostname" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }

        $solusvm->apiCall( 'vserver-hostname', $callArray );
        $r = $solusvm->result;

        $message = '';
        if ( $r["status"] == "success" ) {
            $solusvm->setHostname( $newhostname );
            $message = $_LANG['solusvmplus_hostnameUpdated'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Hostname not specified" ) {
            $message = $_LANG['solusvmplus_enterHostname'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Not supported for this virtualization type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
        $result = (object) array(
            'success' => true,
            'msg'     => $message,
        );
        exit( json_encode( $result ) );

    } else {
        $result = (object) array(
            'success' => false,
            'msg'     => $_LANG['solusvmplus_invalidHostname'],
        );
        exit( json_encode( $result ) );

    }

}

function solusvmplus_Custom_ChangeRootPassword( $params = '' ) {
    global $_LANG;

    $newrootpassword      = $_GET['newrootpassword'];
    $checkNewRootPassword = SolusVM::validateRootPassword( $newrootpassword );
    if ( $checkNewRootPassword ) {
        ## The call string for the connection function
        $callArray = array( "vserverid" => $_GET['vserverid'], "rootpassword" => $newrootpassword );

        $solusvm = new SolusVM( $params );

        if ( $solusvm->getExtData( "clientfunctions" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }
        if ( $solusvm->getExtData( "rootpassword" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }

        $solusvm->apiCall( 'vserver-rootpassword', $callArray );
        $r = $solusvm->result;

        $message = '';
        if ( $r["status"] == "success" ) {
            $solusvm->setCustomfieldsValue( 'rootpassword', $newrootpassword );
            $message = $_LANG['solusvmplus_passwordUpdated'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Root password not specified" ) {
            $message = $_LANG['solusvmplus_enterPassword'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Not supported for this virtualization type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
        $result = (object) array(
            'success' => true,
            'msg'     => $message,
        );
        exit( json_encode( $result ) );

    } else {
        $result = (object) array(
            'success' => false,
            'msg'     => $_LANG['solusvmplus_invalidRootpassword'],
        );
        exit( json_encode( $result ) );

    }

}

function solusvmplus_ontun($params) {
    global $_LANG;

        $callArray = array( "vserverid" => $params['customfields']['vserverid'] );

        $solusvm = new SolusVM( $params );

        if ( $params['configoption15'] != 'Yes' && !isset($_SESSION['adminid'])) {
            
                return $_LANG['solusvmplus_functionDisabled'];
            
        }
        

        $solusvm->apiCall( 'vserver-tun-enable', $callArray );
        $r = $solusvm->result;

        $message = '';
       if ( $r["status"] == "success" ) {
            $message = 'success';
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Not supported for this virtualization type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
return $message;

}

function solusvmplus_offtun($params) {
    global $_LANG;

        $callArray = array( "vserverid" => $params['customfields']['vserverid'] );

        $solusvm = new SolusVM( $params );

        if ( $params['configoption15'] != 'Yes' && !isset($_SESSION['adminid']) ) {

                return $_LANG['solusvmplus_functionDisabled'];
            
        }
        

        $solusvm->apiCall( 'vserver-tun-disable', $callArray );
        $r = $solusvm->result;

        $message = '';
       if ( $r["status"] == "success" ) {
            $message = 'success';
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Not supported for this virtualization type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }

return $message;

}

function solusvmplus_renetwork($params) {
    global $_LANG;

        $callArray = array( "vserverid" => $params['customfields']['vserverid'] );

        $solusvm = new SolusVM( $params );

        if ( $solusvm->getExtData( "renetwork" ) == "disable" ) {
            
                return $_LANG['solusvmplus_functionDisabled'];
            
        }
        

        $solusvm->apiCall( 'vserver-reconfigure-network', $callArray );
        $r = $solusvm->result;

        $message = '';
       if ( $r["status"] == "success" ) {
            $message = 'success';
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Current Virtual Server should have KVM type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
    
return $message;

}

function solusvmplus_ChangePassword($params) {
    global $_LANG;

        $callArray = array( "username" => $params['username'],'password' => $params['password'] );

        $solusvm = new SolusVM( $params );
        
        if ( $solusvm->getExtData( "panelinfo" ) == "disable" ) {
            
                return $_LANG['solusvmplus_functionDisabled'];
            
        }

        $solusvm->apiCall( 'client-updatepassword', $callArray );
        $r = $solusvm->result;

        $message = '';
       if ( $r["status"] == "success" ) {
            $message = 'success';
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
return $message;

}

function solusvmplus_Custom_ChangeVNCPassword( $params = '' ) {
    global $_LANG;

    $newvncpassword      = $_GET['newvncpassword'];
    $checkNewVNCPassword = SolusVM::validateVNCPassword( $newvncpassword );
    if ( $checkNewVNCPassword ) {
        ## The call string for the connection function
        $callArray = array( "vserverid" => $_GET['vserverid'], "vncpassword" => $newvncpassword );

        $solusvm = new SolusVM( $params );

        if ( $solusvm->getExtData( "clientfunctions" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }
        if ( $solusvm->getExtData( "vncpassword" ) == "disable" ) {
            $result = (object) array(
                'success' => false,
                'msg'     => $_LANG['solusvmplus_functionDisabled'],
            );
            exit( json_encode( $result ) );
        }

        $solusvm->apiCall( 'vserver-vncpass', $callArray );
        $r = $solusvm->result;

        $message = '';
        if ( $r["status"] == "success" ) {
            $solusvm->setCustomfieldsValue( 'vncpassword', $newvncpassword );
            $message = $_LANG['solusvmplus_passwordUpdated'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "VNC password not specified" ) {
            $message = $_LANG['solusvmplus_enterPassword'];
        } elseif ( $r["status"] == "error" && $r["statusmsg"] == "Not supported for this virtualization type" ) {
            $message = $_LANG['solusvmplus_virtualizationTypeError'];
        } else {
            $message = $_LANG['solusvmplus_unknownError'];
        }
        //$message = "<PRE>" . print_r($r, true) . $solusvm->debugTxt;
        $result = (object) array(
            'success' => true,
            'msg'     => $message,
        );
        exit( json_encode( $result ) );

    } else {
        $result = (object) array(
            'success' => false,
            'msg'     => $_LANG['solusvmplus_invalidVNCpassword'],
        );
        exit( json_encode( $result ) );

    }

}

function solusvmplus_ClientArea( $params ) {
    $notCustomFuntions = [ 'reboot', 'shutdown', 'boot', 'ontun', 'offtun' , 'renetwork'];
    if ( isset( $_GET['modop'] ) && ( $_GET['modop'] == 'custom' ) ) {
        if ( isset( $_GET['a']) && !in_array( $_GET['a'], $notCustomFuntions ) ) {
            $functionName = 'solusvmplus_' . 'Custom_' . $_GET['a'];
            if ( function_exists( $functionName ) ) {
                $functionName( $params );
            } else {
                $result = (object) array(
                    'success' => false,
                    'msg'     => $functionName . ' not found',
                );
                exit( json_encode( $result ) );

            }
        }
    }
    try {
        $solusvm = new SolusVM( $params );

        $customField = $solusvm->getParam( "customfields" );

        if ( $solusvm->getExtData( "clientfunctions" ) != "disable" ) {

            $solusvm->clientAreaCommands();

            if ( function_exists( 'solusvmplus_customclientarea' ) ) {
                $callArray = array( "vserverid" => $customField["vserverid"], "nographs" => false );
                $solusvm->apiCall( 'vserver-infoall', $callArray );

                if ( $solusvm->result["status"] == "success" ) {
                    $data = $solusvm->clientAreaCalculations( $solusvm->result );
                    
                    return solusvmplus_customclientarea( $params, $data );
                } else {
                    if ( function_exists( 'solusvmplus_customclientareaunavailable' ) ) {
                        $data                  = array();
                        $data["displaystatus"] = "Unavailable";

                        return solusvmplus_customclientareaunavailable( $params, $data );
                    }
                }
            } else {
	            
                $data = array(
                    'vserverid' => $customField["vserverid"],
                );
				
				$callArray = array( "vserverid" => $customField["vserverid"] );
				
				// 获取基本信息
				$solusvm->apiCall( 'vserver-infoall', $callArray );
				$r = $solusvm->result;
				
				$vinfoall = $solusvm->clientAreaCalculations( $r );
				
				// 获取其他信息
				$solusvm->apiCall( 'vserver-info', $callArray );
				$i = $solusvm->result;
				
				$vinfo = $solusvm->clientAreaCalculations2( $i );
				
				// 合并数据
				$info = array_merge($vinfoall,$vinfo);

				// 获取模板
				$typeArray = [ 
					"type" => $info["type"],
					"listpipefriendly" => 'true',
				];
				$solusvm->apiCall( 'listtemplates', $typeArray );
				$t = $solusvm->result;
				
				if ($info["type"] == 'kvm') {
					$tname = explode(',', $t['templateskvm']);
				} else {
					$tname = explode(',', $t['templates']);
				}
				
				$system = [];
				$filename = [];
				foreach ($tname as $key => $value) {
					// 如果是 none 就跳出并且继续下一个循环
					if ($info["type"] != 'kvm') {
						if ($key == '--none--') continue;
					}
					
					// 
					$filename[$key] = $tname[$key];
					$formatName = trim($solusvm->formatOsName( $tname[$key] ));
					
					// 纯名字
					$osname = strtolower(explode(' ', $formatName)['0']);
					$osName = explode(' ', $formatName)['0'];
					
					$system[$osname][$key] = preg_replace("/{$osName} /m", '', $formatName);
				}
				
				//print_r($filename[$key]);die();
				
				// 排序并且序列化数组
				foreach ($system as $key => $value) {
					arsort($system[$key]);
					
					foreach ($system[$key] as $keys => $value) {
						$result[$key][$keys]['name'] = $value;
						$result[$key][$keys]['friendlyname'] = explode('|', $filename[$keys])['1'];
						$result[$key][$keys]['filename'] = explode('|', $filename[$keys])['0'];
					}
				}
				
				//输出结果
				//print_r($result);die();
				//echo json_encode($params);die();
				
				$cp = array(
				    'view' => $solusvm->getExtData( "panelinfo" ),
				    'url' => 'https://'.$params['serverhostname'].':'.$solusvm->GetExtData("port").'/login.php',
				    'username' => $params['username'],
				    'password' => $params['password'],
				    );
				    
                return [
					'overrideDisplayTitle'            => $productName,
					'tabOverviewReplacementTemplate'  => 'templates/clientarea.tpl',
                    'vars' => [
                        'data' 		=> $data,
                        'info' 		=> $info,
                        'userid' 	=> $params['userid'],
                        'rootpass'	=> $customField['rootpassword'],
                        'cp'        => $cp,
                        'params'     => $params,
                        'result'	=> $result,
                    ],
                ];
            }
        }

        return 'false';

    } catch ( Exception $e ) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'Client Area',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}

if ( ! function_exists( 'solusvmplus_customclientareaunavailable' ) ) {
    function solusvmplus_customclientareaunavailable( $params, $cparams ) {
        global $_LANG;
        $output = '
            <div class="row">
                <div class="col-md-3">
                    {$LANG.status}
                </div>
                <div class="col-md-9">
                    <span style="color: #000"><strong>\' . $cparams["displaystatus"] . \'</strong></span>
                </div>
            </div>
        ';

        return $output;
    }
}

