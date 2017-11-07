{include file="../layouts/header.tpl" title="$title"}
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="?p=dash" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>CM</b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>CertManager</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- Logout Button -->
                    <li>
                        <a href="?logout" lass="btn btn-default btn-flat">Sign out</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">MAIN NAVIGATION</li>
                <li {if isset($get.p) && $get.p == "dash"}class="active"{/if}>
                    <a href="?p=dash">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="treeview {if isset($get.p) && $get.p == "certedit" || isset($adddomain.error)}active{/if}">
                    <a href="#">
                        <i class="fa fa-certificate"></i> <span>Zertifikate</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        {foreach from=$domains item=domain}
                            <li {if isset($get.p) && $get.p == "certedit" && isset($get.d) && $get.d == $domain }class="active"{/if}><a href="?p=certedit&d={$domain}"><i class="fa fa-circle-o"></i> {$domain}</a></li>
                        {/foreach}
                        <li style="margin-top: 5px;margin-left: 15px;margin-right: 5px; padding-bottom: 5px;">
                            <form method="post">
                                <div class="form-group {if isset($adddomain.error)}has-error{/if}">
                                    {if isset($adddomain.error)}<label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> Domain Validation Error</label>{/if}
                                        <div class="input-group input-group-sm {if isset($adddomain.error)}has-error{/if}">
                                        <input type="text" name="adddomain" class="form-control" placeholder="mydomain.dtl" value="{if isset($adddomain.value)}{$adddomain.value}{/if}">
                                            <span class="input-group-btn">
                                          <button type="submit" class="btn btn-info btn-flat"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                    {if isset($adddomain.error)}<span class="help-block">Validen Domainname angeben!</span>{/if}
                                </div>

                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        {$__content}
    </div>
    <!-- /.content-wrapper -->
    <!--
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.4.0
        </div>
        <strong>Copyright &copy; 2014-2016 <a href="https://adminlte.io">Almsaeed Studio</a>.</strong> All rights
        reserved.
    </footer>-->
</div>
<!-- ./wrapper -->
{include file="../layouts/fooder.tpl"}