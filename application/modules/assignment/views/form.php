<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div> 
        <div class="form-group">
             <label for="inputRole" class="col-sm-4 control-label"> Role: <span class="text-red">*</span></label>
             <div class="col-sm-8">
             <select name="role" class="form-control select2" style="width: 44%;">  
                 <option value="">Select Role</option>
                      <?php   
foreach ($roles as $role) {
       $selected=(isset($id_role) && $id_role==$role['id']) ? 'selected="selected"' :'';
     	echo '<option value="'.$role['id'].'" '. $selected .'>'.$role['description'].'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div> 
            
            <div class="form-group">
             <label for="inputPerm" class="col-sm-4 control-label"> Permission: <span class="text-red">*</span></label>
             <div class="col-sm-8">
             <select name="perm" class="form-control select2" style="width: 60%;">  
                 <option value="">Select Permission</option>
                      <?php   
foreach ($perms as $perm) {
       $selected=(isset($id_perm) && $id_perm==$perm['id_perm']) ? 'selected="selected"' :'';
     	echo '<option value="'.$perm['id_perm'].'" '. $selected .'>'.$perm['perm_name'].'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>    
            
            
           <div class="form-group">
             <label for="inputGrant" class="col-sm-4 control-label"> Grant: <span class="text-red">*</span></label>
             <div class="col-sm-8">
             <select name="grant" class="form-control select2" style="width: 44%;">  
                 <option value="">Select Grant</option>
      <option value="1" <?php  echo (isset($id_grant) && $id_grant==1) ? 'selected="selected"' :''; ?>>Yes</option>
       <option value="0" <?php  echo (isset($id_grant) && $id_grant==0) ? 'selected="selected"' :''; ?>>No</option>                
                 </select>   
             
             </div>
         </div>          
        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_perm) ? 'Save' : 'Add'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
