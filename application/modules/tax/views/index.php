

                                               <div class="col-xs-11">
   
   <div class="box">
                <div class="box-header">
                  <h6 class="box-title text-green" ><?php echo $message;?></h6>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Options</th>
                      </tr>
                    </thead>
                    <tbody>
              <?php foreach ($taxes  as $tax):?>          
                     	<tr>
            <td><?php echo ($tax->type==1) ? "VAT" : "Commission";?></td>
            <td><?php echo htmlspecialchars($tax->percentage,ENT_QUOTES,'UTF-8');?></td>

            <td><?php echo ($tax->status) ? '<span class="label label-success">'.status_by_id($tax->status).'</span>' : '<span class="label label-danger">'.status_by_id($tax->status).'</span>';?></td>
            <td><?php echo anchor("tax/edit/".$tax->id_tax, '<i class="fa fa-edit"></i>','title="Edit"') ;?> | <?php echo anchor("tax/delete/".$tax->id_tax, '<i class="fa fa-trash"></i>',array('title'=>"Delete",'onclick' => "return confirm('Do you want delete this record')")) ;?></td>
		</tr>
                        
                     <?php endforeach;?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Type</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Options</th>
                      </tr>
                    </tfoot>
                  </table>
                    <p><?php echo anchor('tax/add', '<i class="fa fa-plus-circle"></i> Add Tax')?></p>

                </div><!-- /.box-body -->
                      </div><!-- /.box -->
   
   
   
   </div>