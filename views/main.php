<br><table border="1px" style="height: 100px; width: 500px" >
    <tr>
        <th><?php _e('Parse Status', 'wpsync'); ?></th>
        <th><?php _e('Parsed Products count', 'wpsync'); ?></th>
    </tr>
    <tr style="position: center">
        <td align="center"><?=$args['parse_status']; ?></td>
        <td align="center"><?=$args['parsed_product_counter']; ?></td>
    </tr>
</table>

<div class="start-parse-wrap">
    <?php if ($args['parse_status'] == 'completed') { ?>
        <br><button class="startParse"><?php _e('Start Parse', 'wpsync'); ?></button>
    <?php } else { ?>
        <br><p>In process...</p>
    <?php } ?>
</div>
