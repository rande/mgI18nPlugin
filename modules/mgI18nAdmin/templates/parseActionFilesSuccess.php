<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet
 *
 * Author : Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<?php include_partial('links') ?>


<h1>Message from action files </h1>

<?php foreach($all_results as $filename => $results): ?>
  <h1><?php echo $filename ?></h1>
  <table>
    <?php foreach($results as $result): ?>
      <tr>
        <td colspan="2">&gt;&gt;Line : <?php echo $result['line'] ?></td>
      </tr>
      <tr>
        <td>
          
          Translation : 
          <?php if($result['error']): ?>
            <strong>Error : unable to parse this line</strong>
           <?php else: ?>
            <?php echo __( $result['phrase'], null, $result['catalogue']) ?>
           <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endforeach; ?>