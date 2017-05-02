

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permission</th>
                        <th>Granted</th>
                        </tr>
                </thead>
                <tbody>
                    <?php foreach ($role_perms as $role_perm): ?>          
                        <tr>
                           <td><?php echo $this->ion_auth->group($role_perm->group_id)->row()->description; ?></td>
                           <td><?php echo Modules::run('permission/get_where',$role_perm->perm_id)->row()->perm_name; ?></td>
                            <td><?php echo ($role_perm->value) ? anchor('assignment/remove/' . $role_perm->id, 'Yes',array('title'=>"Remove permission",'onclick' => "return confirm('Do you want to remove permission?')")) : anchor('assignment/grant/' . $role_perm->id, 'No',array('title'=>"Grant permission",'onclick' => "return confirm('Do you want to grant permission?')")); ?></td>
                            </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>

                       <th>Role</th>
                        <th>Permission</th>
                        <th>Granted</th>
                        </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('assignment/add', '<i class="fa fa-plus-circle"></i> Add assignment') ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
