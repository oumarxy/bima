<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div> 
            <div class="form-group">
                <label for="inputBankname" class="col-sm-4 control-label">Bank: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($bank_name); ?></div>
            </div>
            <div class="form-group">
                <label for="inputAccountname" class="col-sm-4 control-label">Name: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($account_name); ?></div>
            </div> 

            <div class="form-group">
                <label class="col-sm-4 control-label">Description: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($description); ?></div>
            </div>  


            <div class="form-group">
                <label for="inputAccountnumber" class="col-sm-4 control-label">Number: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($account_number); ?></div>
            </div>
        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_bank_account) ? 'Save' : 'Add'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
