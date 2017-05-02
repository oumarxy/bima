<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> IBMS | <?php
            $user = $this->ion_auth->user()->row();
            $group = $this->ion_auth->get_users_groups($user->id)->row();
            echo ucfirst($group->description) . ' Panel';
            $level1url = $this->uri->segment(1);
            ?> </title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.4 -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/bootstrap/css/bootstrap.min.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/font-awesome-4.5.0/css/font-awesome.min.css">
        <!-- Ionicons -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/ionicons-2.0.1/css/ionicons.min.css">

        <!-- DataTables -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/plugins/datatables/dataTables.bootstrap.css">

        <!-- daterange picker -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/plugins/daterangepicker/daterangepicker-bs3.css">
        <!-- iCheck for checkboxes and radio inputs -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/plugins/iCheck/all.css">

        <!-- Select2-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/plugins/select2/select2.min.css">

        <!-- Theme style -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/dist/css/AdminLTE.min.css">
        <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
              page. However, you can choose any other skin. Make sure you
              apply the skin class to the body tag so the changes take effect.
        -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/dist/css/skins/skin-blue.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>media/dist/css/skins/_all-skins.min.css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>
    <body class="skin-blue sidebar-mini">
        <div class="wrapper">

            <!-- Main Header -->
            <header class="main-header">

                <!-- Logo -->
                <?php echo anchor('site', '<span class="logo-mini"><b>IBMS</b></span><span class="logo-lg"><b>InsuranceBrokers</b></span>', 'class="logo"'); ?>

                <!-- Header Navbar -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">

                            <!-- Notifications Menu -->
                            <li class="dropdown notifications-menu">
                                <!-- Menu toggle button -->
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-bell-o"></i>
                                    <span class="label label-warning">10</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header">You have 10 notifications</li>
                                    <li>
                                        <!-- Inner Menu: contains the notifications -->
                                        <ul class="menu">
                                            <li><!-- start notification -->
                                                <a href="#">
                                                    <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                                </a>
                                            </li><!-- end notification -->
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="#">View all</a></li>
                                </ul>
                            </li>

                            <!-- User Account Menu -->
                            <li class="dropdown user user-menu">
                                <!-- Menu Toggle Button -->
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <!-- The user image in the navbar-->
                                    <?php $imgurl = file_exists("uploads/user_image/" . $user->id . ".jpg") ? base_url() . "uploads/user_image/" . $user->id . ".jpg" : base_url() . "uploads/user_image/noimage.jpg" ?>
                                    <img src="<?php echo $imgurl ?>" class="user-image" alt="User Image">
                                  <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                    <span class="hidden-xs"><?php echo $user->first_name . ' ' . $user->last_name; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- The user image in the menu -->
                                    <li class="user-header">
                                        <img src="<?php echo $imgurl ?>" class="img-circle" alt="User Image">

                                        <p>
                                            <?php echo $user->first_name . ' ' . $user->last_name; ?>
                                            <small>Last Login <?php
                                            $datestring = "%M %d, %Y at %H:%i:%s";
                                            echo format_date($datestring, $user->last_login);
                                            ?></small>
                                        </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <?php echo anchor('user/profile/' . $user->id, 'Your Profile', 'class="btn btn-default btn-flat"') ?>
                                        </div>
                                        <div class="pull-right">
                                            <?php echo anchor('site/logout', 'Logout', 'class="btn btn-default btn-flat"') ?>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">

                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">

                    <!-- Sidebar Menu -->
                    <ul class="sidebar-menu">
                        <li class="header">MAIN MENU</li>
                        <!-- Optionally, you can add icons to the links -->
                        <li ><?php echo anchor('site', '<i class="fa fa-home"></i> <span>Dashboard</span>') ?></li>
                        <li class="treeview <?php echo isset($customer_active) ? $customer_active : ''; ?>">
                            <a href="#"><i class="fa fa-users"></i> <span>Customer</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($customer_information_active) ? 'class="' . $customer_information_active . '"' : ''; ?>><?php echo anchor('customer', '<i class="fa fa-folder-open-o"></i> Customer Information') ?></li>
                                <li <?php echo isset($import_customer_active) ? 'class="' . $import_customer_active . '"' : ''; ?>><?php echo anchor('customer/import', '<i class="fa fa-upload"></i> Import Customer') ?></li>
                                <li <?php echo isset($register_customer_active) ? 'class="' . $register_customer_active . '"' : ''; ?>><?php echo anchor('customer/register', '<i class="fa fa-plus-circle"></i> Register Customer') ?></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($insurance_active) ? $insurance_active : ''; ?>">
                            <a href="#"><i class="fa fa-umbrella"></i> <span>Insurance</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($claim_sheet_active) ? 'class="' . $claim_sheet_active . '"' : ''; ?>><?php echo anchor('claim/sheet', '<i class="fa fa-file"></i> Claim Sheet') ?></li>
                                <li <?php echo isset($import_bulk_insurance_active) ? 'class="' . $import_bulk_insurance_active . '"' : ''; ?>><?php echo anchor('insurance/import', '<i class="fa fa-upload"></i> Import Bulk Insurance') ?></li>
                                <li <?php echo isset($import_renewal_insurance_active) ? 'class="' . $import_renewal_insurance_active . '"' : ''; ?>><?php echo anchor('insurance/import_renewal', '<i class="fa fa-upload"></i> Import Renewal Insurance') ?></li>
                                <li <?php echo isset($insurance_information_active) ? 'class="' . $insurance_information_active . '"' : ''; ?>><?php echo anchor('insurance', '<i class="fa fa-folder-open-o"></i> Insurance Information') ?></li>
                                <li <?php echo isset($property_information_active) ? 'class="' . $property_information_active . '"' : ''; ?>><?php echo anchor('property', '<i class="fa fa-car"></i> Property Information') ?></li>

                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($financial_active) ? $financial_active : ''; ?>">
                            <a href="#"><i class="fa fa-money"></i> <span>Financial</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($add_payment_active) ? 'class="' . $add_payment_active . '"' : ''; ?>><?php echo anchor('payment/add', '<i class="fa fa-plus-circle"></i> Add Payment') ?></li>
                                <li <?php echo isset($bank_transaction_active) ? 'class="' . $bank_transaction_active . '"' : ''; ?>><?php echo anchor('bank_transaction', '<i class="fa fa-bank"></i> Bank Transaction') ?></li>
                                <li <?php echo isset($payment_sheet_active) ? 'class="' . $payment_sheet_active . '"' : ''; ?>><?php echo anchor('payment/sheet', '<i class="fa fa-book"></i> Payment Sheet') ?></li>
                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($report_active) ? $report_active : ''; ?>">
                            <a href="#"><i class="fa fa-bar-chart"></i> <span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($claim_report_active) ? 'class="' . $claim_report_active . '"' : ''; ?>><?php echo anchor('report/claim_preview', '<i class="fa fa-area-chart"></i> Claim Report') ?></li>
                                <li <?php echo isset($insurance_report_active) ? 'class="' . $insurance_report_active . '"' : ''; ?>><?php echo anchor('report/insurance_preview', '<i class="fa fa-line-chart"></i> Insurance Report') ?></li>
                                <li <?php echo isset($premium_collection_report_active) ? 'class="' . $premium_collection_report_active . '"' : ''; ?>><?php echo anchor('report/premium_collection', '<i class="fa  fa-pie-chart"></i> Premium Collection') ?></li>
                                <li <?php echo isset($remmitance_report_active) ? 'class="' . $remmitance_report_active . '"' : ''; ?>><?php echo anchor('report/premium_remmitance', '<i class="fa  fa-pie-chart"></i> Premium Remmitance') ?></li>
                                <li <?php echo isset($quarter_return_report_active) ? 'class="' . $quarter_return_report_active . '"' : ''; ?>><?php echo anchor('report/quarter_return', '<i class="fa  fa-pie-chart"></i> Quarter Return') ?></li>
                                <li <?php echo isset($bank_transaction_active) ? 'class="' . $bank_transaction_active . '"' : ''; ?>><?php echo anchor('report/bank_transaction', '<i class="fa  fa-bank"></i> Bank Transaction') ?></li>

                            </ul>
                        </li>
                        <li class="treeview <?php echo isset($setting_active) ? $setting_active : ''; ?>">
                            <a href="#"><i class="fa fa-cog"></i> <span>Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($bank_account_active) ? 'class="' . $bank_account_active . '"' : ''; ?>><?php echo anchor('bank_account', '<i class="fa fa-bank"></i>Bank Account') ?></li>
                                <li <?php echo isset($cover_type_active) ? 'class="' . $cover_type_active . '"' : ''; ?>><?php echo anchor('cover_type', '<i class="fa fa-wrench"></i>Cover Type') ?></li>
                                <li <?php echo isset($claim_type_active) ? 'class="' . $claim_type_active . '"' : ''; ?>><?php echo anchor('claim_type', '<i class="fa fa-circle-o"></i>Claim Type') ?></li>
                                <!--   <li <?php echo isset($vehicle_class_active) ? 'class="' . $vehicle_class_active . '"' : ''; ?>><?php echo anchor('vehicle_class', '<i class="fa fa-bus"></i>Vehicle Class') ?></li>-->
                                <li <?php echo isset($insurer_active) ? 'class="' . $insurer_active . '"' : ''; ?>><?php echo anchor('insurer', '<i class="fa fa-umbrella"></i>Insurer') ?></li>
                                <li <?php echo isset($tax_active) ? 'class="' . $tax_active . '"' : ''; ?>><?php echo anchor('tax', '<i class="fa fa-balance-scale"></i>Tax') ?></li>

                            </ul>
                        </li>

                        <li class="treeview <?php echo isset($users_active) ? $users_active : ''; ?>">
                            <a href="#"><i class="fa fa-lock"></i> <span>Users</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($create_user_active) ? 'class="' . $create_user_active . '"' : ''; ?>><?php echo anchor('user/create', '<i class="fa fa-user-plus"></i>Create User') ?></li>
                                <li <?php echo isset($list_users_active) ? 'class="' . $list_users_active . '"' : ''; ?>><?php echo anchor('user', '<i class="fa fa-user"></i>List Users') ?></li>
                                <li <?php echo isset($user_profile_active) ? 'class="' . $user_profile_active . '"' : ''; ?>><?php echo anchor('user/profile/' . $user->id, '<i class="fa fa-user-secret"></i>User Profile') ?></li>
                            </ul>
                        </li>


                        <li class="treeview <?php echo isset($role_management_active) ? $role_management_active : ''; ?>">
                            <a href="#"><i class="fa fa-wrench"></i> <span>Roles & Maintenances</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li <?php echo isset($assignment_active) ? 'class="' . $assignment_active . '"' : ''; ?>><?php echo anchor('assignment', '<i class="fa fa-user-secret"></i>Assignment') ?></li>
                                <li <?php echo isset($permission_active) ? 'class="' . $permission_active . '"' : ''; ?>><?php echo anchor('permission', '<i class="fa fa-user-secret"></i>Permission') ?></li>
                                <li <?php echo isset($db_backup_active) ? 'class="' . $db_backup_active . '"' : ''; ?>><?php echo anchor('maintenance/db_backup', '<i class="fa fa-database"></i>Database Backup') ?></li>
                            </ul>
                        </li>
                        <li><?php echo anchor('site/logout', '<i class="fa fa-sign-out""></i> <span>Logout</span>') ?></li>
                    </ul><!-- /.sidebar-menu -->
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php echo ucwords($heading) ?>
                        <small><?php echo strtolower($subheading) ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><?php echo anchor('site', '<i class="fa fa-home"></i>Home') ?></li>
                        <li><?php
                        echo anchor($level1url, ucfirst($level1url))
                        ?></li>
                            <?php if ($this->uri->segment(2) <> '') { ?>
                            <li class="active"><?php echo quick_navigation_menu($this->uri->segment(2)); ?></li>
                        <?php } ?>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <!-- Your Page Content Here -->
