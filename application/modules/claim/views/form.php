<div class="col-md-1"></div>
<div class="col-md-6">
 <div class="box box-info">      
<?php echo form_open(uri_string(),'class="form-horizontal"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div> 
      <div class="form-group">
          <label for="inputCoverNote" class="col-sm-4 control-label">Cover Note: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($cover_note);?></div>
      </div>
         
     <div class="form-group">
          <label for="inputReportedon" class="col-sm-4 control-label">Reported Date: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($reported_on);?></div>
      </div>    
         
      <div class="form-group">
          <label for="inputLoston" class="col-sm-4 control-label">Lost Date: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($lost_on);?></div>
      </div>    
         
      <div class="form-group">
             <label for="inputClaimtype" class="col-sm-4 control-label"> Nature: <span class="text-red">*</span></label>
             <div class="col-sm-8">
             <select name="claim_type" class="form-control select2" style="width: 44%;">    
                 <option>Select nature</option>
                      <?php   
foreach ($claim_type_list as $claim_type) {
       $selected=(isset($id_claim_type) && $claim_type['id_claim_type']==$id_claim_type) ? 'selected="selected"' :'';
     	echo '<option value="'.$claim_type['id_claim_type'].'" '. $selected .'>'.$claim_type['type'].'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>  
         
         
          
       <div class="form-group">
          <label for="inputNumber" class="col-sm-4 control-label">Number: </label>
          <div class="col-sm-8"><?php echo form_input($claim_number);?></div>
      </div>      
         
       <div class="form-group">
          <label for="inputAmount" class="col-sm-4 control-label">Amount: </label>
          <div class="col-sm-8"><?php echo form_input($claim_amount);?></div>
      </div>  
          
  <div class="form-group">
          <label for="inputSettlement" class="col-sm-4 control-label">Settlement: </label>
          <div class="col-sm-8"><?php echo form_input($settlement_amount);?></div>
      </div>  
          
         
     </div>
    

       <div class="box-footer">
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_claim) ? 'Save' : 'Register';?></button>
      </div><!-- /.box-footer -->
<?php echo form_close();?>
</div>
</div>
      <div class="col-md-5"></div>
      