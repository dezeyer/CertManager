<section class="content-header">
    <h1>
        Zertifikatverteilung anpassen
        <small>{if isset($get.d)}{$get.d}{/if}</small>
    </h1>
</section>
{if isset($error)}
    {$error}
{else}
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements disabled -->
                <form role="form" method="post">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Validierungsart</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                                <!-- select -->
                                <div class="form-group">
                                    <select class="form-control" name="VALIDATION">
                                        <option {if isset($params.VALIDATE_VIA_DNS) && $params.VALIDATE_VIA_DNS == "true"}selected{/if} value="0">VALIDATE_VIA_DNS</option>
                                        <option {if isset($params.USE_SINGLE_ACL) && $params.USE_SINGLE_ACL == "true"}selected{/if} value="1">USE_SINGLE_ACL</option>
                                        <option {if isset($params.USE_SINGLE_ACL) && $params.USE_SINGLE_ACL == "false"}selected{/if} value="2">USE_MULTIPLE_ACL</option>
                                    </select>
                                </div>

                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="reset" class="btn btn-default">Zurücksetzen</button>
                            <button type="submit" name="updateconf" value="1" class="btn btn-info pull-right">Übernehmen</button>
                        </div>
                    </div>
                </form>
                <!-- /.box -->
                <form role="form" method="post">
                    <!-- general form elements disabled -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Validierungspfade</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            {*Choose DNS Config*}
                            {if isset($params.VALIDATE_VIA_DNS) && $params.VALIDATE_VIA_DNS == "true"}
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <!-- select -->
                                    <div class="form-group">
                                        <label>DNS-Provider</label>
                                        <select class="form-control" name="DNSCONF">
                                            {foreach from=$dnsscripts item=script key=key}
                                                <option value="{$key}">{$script.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            {/if}

                            {*Choose Single ACL Config*}
                            {if isset($params.USE_SINGLE_ACL) && $params.USE_SINGLE_ACL == "true"}
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <!-- select -->
                                    {if $showhelp}
                                        <pre># Acme Challenge Location.
/path/to/your/website/folder/.well-known/acme-challenge
ssh:server5:/var/www/{$get.d}/web/.well-known/acme-challenge
ssh:sshuserid@server5:/var/www/{$get.d}/web/.well-known/acme-challenge
ftp:ftpuserid:ftppassword:{$get.d}:/web/.well-known/acme-challenge</pre>
                                    {/if}
                                    <div class="form-group">
                                        <label>ACL für alle Domains</label>
                                        <input type="text" name="ACL[0]" class="form-control" placeholder="ssh:server5:/var/www/{$get.d}/web/.well-known/acme-challenge" {if isset($params.ACL.0)}value="{$params.ACL.0}"{/if}>
                                    </div>
                                </div>
                            {/if}

                            {*Choose Multiple ACL Config*}
                            {if isset($params.USE_SINGLE_ACL) && $params.USE_SINGLE_ACL == "false"}
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <!-- select -->
                                    {if $showhelp}
                                    <pre># Acme Challenge Location.
/path/to/your/website/folder/.well-known/acme-challenge
ssh:server5:/var/www/{$get.d}/web/.well-known/acme-challenge
ssh:sshuserid@server5:/var/www/{$get.d}/web/.well-known/acme-challenge
ftp:ftpuserid:ftppassword:{$get.d}:/web/.well-known/acme-challenge</pre>
                                    {/if}
                                    <div class="form-group">
                                        <label>ACL für {$get.d}</label>
                                        <input type="text" name="ACL[0]" class="form-control" placeholder="ssh:server5:/var/www/{$get.d}/web/.well-known/acme-challenge" {if isset($params.ACL.0)}value="{$params.ACL.0}"{/if}>
                                    </div>
                                    {foreach from=$params.SANS key=key item=SAN}
                                        <div class="form-group">
                                            <label>ACL für {$SAN}</label>
                                            <input type="text" name="ACL[{$key+1}]" class="form-control" placeholder="ssh:server5:/var/www/{$SAN}/web/.well-known/acme-challenge" {if isset($params.ACL.{$key+1})}value="{$params.ACL.{$key+1}}"{/if}>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="reset" class="btn btn-default">Zurücksetzen</button>
                            <button type="submit" name="updateconf" value="1" class="btn btn-info pull-right">Übernehmen</button>
                        </div>
                    </div>
                    <!-- /.box -->
                </form>
                <form role="form" method="post">
                    <!-- general form elements disabled -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Zertifikat Verteilen</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- /.box-header -->
                            <div class="box-body">
                                <!-- select -->
                                {if $showhelp}
                                <pre># Location for all your certs, these can either be on the server (full path name)
# or using ssh /sftp as for the ACL
/etc/ssl/{$get.d}.key
ssh:server5:/etc/ssl/{$get.d}.crt
ssh:sshuserid@server5:/etc/ssl/{$get.d}.crt
ssh:sshuserid@server5:/etc/ssl/{$get.d}.key
ftp:ftpuserid:ftppassword:/etc/ssl/{$get.d}.key</pre>{/if}
                                <div class="form-group">
                                    <label>Domain Zertifikat Pfad</label>
                                    <input type="text" name="DOMAIN_CERT_LOCATION" class="form-control" placeholder="ssh:server5:/etc/ssl/{$get.d}.crt" value="{if isset($params.CA_CERT_LOCATION)}{$params.DOMAIN_CERT_LOCATION}{/if}">
                                </div>
                                <div class="form-group">
                                    <label>Domain Key Pfad</label>
                                    <input type="text" name="DOMAIN_KEY_LOCATION" class="form-control" placeholder="ssh:server5:/etc/ssl/{$get.d}.key" value="{if isset($params.CA_CERT_LOCATION)}{$params.DOMAIN_KEY_LOCATION}{/if}">
                                </div>
                                <div class="form-group">
                                    <label>CA Zertifikat Pfad</label>
                                    <input type="text" name="CA_CERT_LOCATION" class="form-control" placeholder="ssh:server5:/etc/ssl/{$get.d}.key" value="{if isset($params.CA_CERT_LOCATION)}{$params.CA_CERT_LOCATION}{/if}">
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label>Reload CMD</label>
                                    <input type="text" name="RELOAD_CMD" class="form-control" placeholder="service apache2 restart" value="{if isset($params.RELOAD_CMD)}{$params.RELOAD_CMD}{/if}">
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="reset" class="btn btn-default">Zurücksetzen</button>
                            <button type="submit" name="updateconf" value="1" class="btn btn-info pull-right">Übernehmen</button>
                        </div>
                    </div>
                    <!-- /.box -->
                </form>
            </div>
            <!--/.col (left) -->
            <!-- right column -->
            <div class="col-md-6">
                <!-- general form elements disabled -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Zertifikatsinfo</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <pre>{$certinfo}</pre>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <a type="button" class="btn btn-default" href="?p={$get.p}&d={$get.d}&delcert">Zertifikat löschen</a>
                        <button type="button" id="getcert" class="btn btn-info pull-right" data-toggle="modal" data-target="#modal-default">Zertifikat erneuern/anlegen</button>
                    </div>
                </div>
                <!-- /.box -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">SANS Verwalten</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        {if isset($SANupdateError)}
                        <div class="callout callout-danger">
                            <p>{$SANupdateError}</p>
                        </div>
                        {/if}
                        {if isset($SANupdateInfo)}
                        <div class="callout callout-info">
                            <p>{$SANupdateInfo}</p>
                        </div>
                        {/if}
                        {foreach from=$params.SANS key=key item=SAN}
                            <form method="post" role="form">
                                <div class="form-group">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="SANS[{$key}]" class="form-control" placeholder="{$SAN}" value="{$SAN}">
                                        <span class="input-group-btn">
                                          <button type="submit" class="btn btn-info btn-flat"><i class="fa fa-save"></i></button>
                                          <a href="?p=certedit&d={$get.d}&delsan={$key}" type="button" class="btn btn-danger btn-flat"><i class="fa fa-minus"></i></a>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        {/foreach}
                        <hr>
                        <form method="post">
                            <div class="form-group">
                                <label>Neue SAN anlegen</label><label class="pull-right">SAN hinzufügen</label>
                                {if isset($SANaddError)}
                                    <div class="callout callout-danger">
                                    <p>{$SANaddError}</p>
                                    </div>
                                {/if}
                                {if isset($SANaddInfo)}
                                    <div class="callout callout-info">
                                    <p>{$SANaddInfo}</p>
                                    </div>
                                {/if}
                                <div class="input-group input-group-sm">
                                    <input type="text" name="addsan" value="{if isset($post.addsan)}{$post.addsan}{/if}" class="form-control">
                                    <span class="input-group-btn">
                                      <button type="submit" class="btn btn-info btn-flat"><i class="fa fa-plus"></i></button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.box -->
                <form method="post" role="form">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Notiz</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label>Es ist immer gut sich etwas notieren zu können</label>
                                <textarea class="form-control" name="note" rows="3" placeholder="...">{if isset($params.note)}{$params.note}{/if}</textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="reset" class="btn btn-default">Zurücksetzen</button>
                            <button type="submit" name="updateconf" value="1" class="btn btn-info pull-right">Übernehmen</button>
                        </div>
                    </div>
                </form>
                <!-- /.box -->
            </div>
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
        <div>
    </section>
    <div class="modal fade" id="modal-default">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">getssl -f Log</h4>
                </div>
                <div class="modal-body">
                    <pre id="getssloutput">Bitte warten....</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" id="killgetcert">Stop</button>
                    <button type="button" class="btn btn-primary" id="forcegetcert">Erneuern erzwingen</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
{/if}