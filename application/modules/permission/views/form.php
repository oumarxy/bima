<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div> 
            <div class="form-group">
                <label for="inputPermKey" class="col-sm-4 control-label">Key: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($perm_key); ?></div>
            </div>

            <div class="form-group">
                <label for="inputPermName" class="col-sm-4 control-label">Description: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($perm_name); ?></div>
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
