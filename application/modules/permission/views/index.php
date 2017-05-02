

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Permission Description</th>
                        <th>Permission Key</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perms as $perm): ?>          
                        <tr>
                           <td><?php echo $perm->perm_name; ?></td>
                            <td><?php echo $perm->perm_key; ?></td>
                            <td><?php echo anchor("permission/edit/" . $perm->id_perm, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> | <?php echo anchor("permission/delete/" . $perm->id_perm, '<i class="fa fa-trash"></i>',  array('title'=>"Delete",'onclick' => "return confirm('Do you want to delete this record?')")); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>

                        <th>Permission Description</th>
                        <th>Permission Key</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('permission/add', '<i class="fa fa-plus-circle"></i> Add permission') ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
