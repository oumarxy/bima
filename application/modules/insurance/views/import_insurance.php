<div class="col-md-1"></div>
<div class="col-md-4">   
    <div class="box box-info">
        <?php echo form_open_multipart(uri_string(), 'role="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div>
            <?php echo form_hidden('post_data', '1'); ?>
            <div class="form-group">
                <p class="help-block">Upload file and import <br/> 
                    <?php echo anchor('../uploads/template/insurance_import.xlsx', 'Download import template', 'target="_blank"'); ?><br/>

                </p>
                <label for="excelInputFile">File Input</label>
                <?php echo form_upload('userfile'); ?>
            </div>
            <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Insurer: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="insurer" class="form-control select2" style="width: 100%;">
                        <option value="">Select Insurer</option>
                        <?php
                        foreach ($insurer_list as $insurer) :
                            $selected = (isset($id_insurer) && $id_insurer == $insurer['id_insurer']) ? 'selected="selected"' : '';
                            echo '<option value="' . $insurer['id_insurer'] . '" ' . $selected . '>' . $insurer['name'] . '</option>';
                        endforeach;
                        ?>    

                    </select>   

                </div>
            </div>
        </div>



        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right">Import</button>
        </div><!-- /.box-footer -->

        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-7"></div>
