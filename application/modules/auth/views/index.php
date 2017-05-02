
<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th><?php echo lang('index_email_th'); ?></th>
                        <?php if ($this->ion_auth->is_admin())  ?>
                        <th>Domain</th>
                        <th>Role</th>
                        <th>Picture</th>
                        <th><?php echo lang('index_status_th'); ?></th>
                        <th>Last Login</th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user->first_name.' '.$user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                              <?php if ($this->ion_auth->is_admin())  ?>
                            <td><?php echo Modules::run('domain/get_where',$user->id_domain)->row()->name ; ?></td>
                            <td>
                                <?php foreach ($user->groups as $group): ?>
                                    <?php echo anchor("group/edit/" . $group->id, htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8')); ?><br />
                                <?php endforeach ?>
                            </td>
                           <td>
                             <?php $imgurl = file_exists("uploads/user_image/" . $user->id . ".jpg") ? base_url() . "uploads/user_image/" . $user->id . ".jpg" : base_url() . "uploads/user_image/noimage.jpg" ?>
                             <img class="profile-user-img img-responsive img-circle" src="<?php echo $imgurl ?>" alt="User profile picture" width="30" height="30">
                             </td>

                            <td><?php echo ($user->active) ? anchor("user/deactivate/" . $user->id, '<span class="label label-success">' . lang('index_active_link') . '</span>') : anchor("user/activate/" . $user->id, '<span class="label label-danger">' . lang('index_inactive_link') . '</span>'); ?></td>
                            <td><?php $datestring = "%d/%m/%Y %H:%i:%s";
                            echo format_date($datestring, $user->last_login); ?></td>
                            <td><?php echo anchor("user/edit/" . $user->id, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?></td>
                        </tr>

<?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                      <th>Name</th>
                      <th><?php echo lang('index_email_th'); ?></th>
                      <?php if ($this->ion_auth->is_admin())  ?>
                      <th>Domain</th>
                      <th>Role</th>
                      <th>Picture</th>
                      <th><?php echo lang('index_status_th'); ?></th>
                      <th>Last Login</th>
                      <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('user/create', '<i class="fa fa-plus-circle"></i> ' . lang('index_create_user_link')) ?> | <?php echo anchor('group/create', '<i class="fa fa-plus-circle"></i> ' . lang('index_create_group_link')) ?></p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
