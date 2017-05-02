<div class="col-md-1"></div>
<div class="col-md-4">
    <div class="box box-info">     
    <!--report/print_quarter_return_excel--> 
        <?php echo form_open('', 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php //echo $message; ?>
            </div>  

            <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Ended Month: </label>
                <div class="col-sm-8">
                    <select name="end_month" class="form-control select2" style="width: 55%;">   

                        <option value="3" > March </option>
                        <option value="6" > June </option>
                        <option value="9" >September </option>
                        <option value="12" > December </option>
                    </select>   

                </div>
            </div>       
        </div>


        <div class="box-footer text-center">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right">Print Excel</button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-7"></div>