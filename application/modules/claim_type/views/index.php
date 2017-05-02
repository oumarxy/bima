

                                               <div class="col-xs-11">
   
   <div class="box">
                <div class="box-header">
                  <h6 class="box-title text-green" ><?php echo $message;?></h6>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th><?php echo 'Type';?></th>
                         <th><?php echo 'Description';?></th>
                        <th><?php echo  'Status';?></th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
              <?php foreach ($claim_types  as $claim_type):?>          
                     	<tr>
            <td><?php echo htmlspecialchars($claim_type->type,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($claim_type->description,ENT_QUOTES,'UTF-8');?></td>
       

            <td><?php echo ($claim_type->status) ? '<span class="label label-success">'.status_by_id($claim_type->status).'</span>' : '<span class="label label-danger">'.status_by_id($claim_type->status).'</span>';?></td>
			<td><?php echo anchor("claim_type/edit/".$claim_type->id_claim_type, '<i class="fa fa-edit"></i>','title="Edit"') ;?> | <?php echo anchor("claim_type/delete/".$claim_type->id_claim_type, '<i class="fa fa-trash"></i>',array('title'=>"Delete",'onclick' => "return confirm('Do you want delete this record')")) ;?></td>
		</tr>
                        
                     <?php endforeach;?>
                    </tbody>
                    <tfoot>
                      <tr>
                         <th><?php echo 'Type';?></th>
                          <th><?php echo 'Description';?></th>
                        <th><?php echo 'Status';?></th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                    <p><?php echo anchor('claim_type/add', '<i class="fa fa-plus-circle"></i> Add claim type')?></p>

                </div><!-- /.box-body -->
                      </div><!-- /.box -->
   
   
   
   </div>