<div class="col-md-1"></div>    
<div class="col-md-6">
  <div class="box box-info">    

<?php echo form_open_multipart(uri_string(),'class="form-horizontal"');?>
 <div class="box-body">
  <div class="form-group has-error text-red text-center"><?php echo $message;?></div>  
     <div class="form-group">
         <label for="inputFirstname" class="col-sm-4 control-label"> <?php echo lang('edit_user_fname_label', 'first_name');?></label>
         <div class="col-sm-8"> <?php echo form_input($first_name);?></div>     
     </div>

        <div class="form-group">
            <label for="inputLastname" class="col-sm-4 control-label"><?php echo lang('edit_user_lname_label', 'last_name');?></label>
            <div class="col-sm-8"><?php echo form_input($last_name);?></div>
        </div>

         <div class="form-group">
             <label for="inputPhone" class="col-sm-4 control-label"> <?php echo lang('edit_user_phone_label', 'phone');?>:</label>
             <div class="col-sm-8"> <?php echo form_input($phone);?></div>
         </div>

          <div class="form-group">
              <label for="inputPassword" class="col-sm-4 control-label"><?php echo lang('edit_user_password_label', 'password');?>:</label>
              <div class="col-sm-8"><?php echo form_input($password);?></div>
          </div>

         <div class="form-group">
             <label for="inputPasswordconfirm" class="col-sm-4 control-label"><?php echo lang('edit_user_password_confirm_label', 'password_confirm');?>:</label>
             <div class="col-sm-8"><?php echo form_input($password_confirm);?></div>
         </div>
      <?php if ($this->ion_auth->is_admin()): ?>
         <div class="form-group">
             <label for="inputGroup" class="col-sm-4 control-label"> <?php echo lang('edit_user_groups_heading');?>:</label>
             <div class="col-sm-8">
                 <select name="groups[]" class="form-control select2" style="width: 44%;">
                      <?php 	    
     foreach($groups as $group){
     $gID=$group['id'];  
     $selected = null;
         foreach($currentGroups as $grp) {
                      if ($gID == $grp->id) {
                          $selected= ' selected="selected"';
                      break;
                      }
                  }
     	echo '<option value="'.$group['id'].'" '. $selected .'>'.$group['name'].'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>
     <?php endif ?>
          <div class="form-group">
                  <label for="inputPhoto" class="col-sm-4 control-label">Photo:</label>

                    <div class="col-sm-5">
                     <input type="file" name="userfile" accept="image/*">     
                    </div>
                </div>
      <?php echo form_hidden('id', $user->id);?>
      <?php echo form_hidden($csrf); ?>
       </div>
     <div class="box-footer">
                     <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
                    <button type="submit" class="btn btn-info pull-right"><?php echo  lang('edit_user_submit_btn');?></button>
                  </div><!-- /.box-footer -->
<?php echo form_close();?>
  </div>
</div>
<div class="col-md-5"></div>  