<div class="col-md-1"></div>
<div class="col-md-6">
 <div class="box box-info">      

<?php echo form_open('user/create")','class="form-horizontal"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div> 
      <div class="form-group">
          <label for="inputFirstname" class="col-sm-4 control-label"><?php echo lang('create_user_fname_label', 'first_name');?>  <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($first_name);?></div>
      </div>

     <div class="form-group">
         <label for="inputLaststname" class="col-sm-4 control-label"><?php echo lang('create_user_lname_label', 'last_name');?>  <span class="text-red">*</span></label>     
         <div class="col-sm-8">  <?php echo form_input($last_name);?></div>
     </div>

     <div class="form-group">
         <label for="inputEmail" class="col-sm-4 control-label"><?php echo lang('create_user_email_label', 'email');?>  <span class="text-red">*</span></label>
         <div class="col-sm-8"> <?php echo form_input($email);?></div>
      </div>

       <div class="form-group">
           <label for="inputPhone" class="col-sm-4 control-label"> <?php echo lang('create_user_phone_label', 'phone');?>  <span class="text-red">*</span></label>
           <div class="col-sm-8"> <?php echo form_input($phone);?></div>
       </div>

       <div class="form-group">
           <label for="inputPassword" class="col-sm-4 control-label">  <?php echo lang('create_user_password_label', 'password');?>  <span class="text-red">*</span></label>
           <div class="col-sm-8"> <?php echo form_input($password);?></div>
       </div>

     <div class="form-group">
         <label for="inputPasswordconfirm" class="col-sm-4 control-label">     <?php echo lang('create_user_password_confirm_label', 'password_confirm');?>  <span class="text-red">*</span></label>
         <div class="col-sm-8"> <?php echo form_input($password_confirm);?></div>
     </div>
     </div>
       <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo  lang('create_user_submit_btn');?></button>
      </div><!-- /.box-footer -->
      
<?php echo form_close();?>
</div>
</div>
      <div class="col-md-5"></div>