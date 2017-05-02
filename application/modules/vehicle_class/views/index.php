

                                               <div class="col-xs-11">
   
   <div class="box">
                <div class="box-header">
                  <h6 class="box-title text-green" ><?php echo $message;?></h6>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th><?php echo 'Class';?></th>
                        <th><?php echo 'Description';?></th>
                        <th><?php echo 'Status';?></th>
                        <th>Options</th>
                      </tr>
                    </thead>
                    <tbody>
              <?php foreach ($vehicle_classes  as $vehicle_class):?>          
                     	<tr>
            <td><?php echo htmlspecialchars($vehicle_class->class,ENT_QUOTES,'UTF-8');?></td>
               <td><?php echo htmlspecialchars($vehicle_class->description,ENT_QUOTES,'UTF-8');?></td>
       

            <td><?php echo ($vehicle_class->status) ? '<span class="label label-success">'.status_by_id($vehicle_class->status).'</span>' : '<span class="label label-danger">'.status_by_id($vehicle_class->status).'</span>';?></td>
            <td><?php echo anchor("vehicle_class/edit/".$vehicle_class->id_vehicle_class, '<i class="fa fa-edit"></i>','title="Edit"') ;?> | <?php echo anchor("vehicle_class/delete/".$vehicle_class->id_vehicle_class, '<i class="fa fa-trash"></i>',array('title'=>"Delete",'onclick' => "return confirm('Do you want delete this record')")) ;?></td>
		</tr>
                        
                     <?php endforeach;?>
                    </tbody>
                    <tfoot>
                      <tr>
                         <th><?php echo 'Class';?></th>
                         <th><?php echo 'Description';?></th>
                        <th><?php echo 'Status';?></th>
                        <th>Options</th>
                      </tr>
                    </tfoot>
                  </table>
                    <p><?php echo anchor('vehicle_class/add', '<i class="fa fa-plus-circle"></i> Add Vehicle Class')?></p>

                </div><!-- /.box-body -->
                      </div><!-- /.box -->
   
   
   
   </div>