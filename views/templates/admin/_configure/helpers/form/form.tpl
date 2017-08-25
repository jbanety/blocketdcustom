{**
 * @package     blocketdcustom
 *
 * @version     2.0
 * @copyright   Copyright (C) 2017 ETD Solutions. Tous droits réservés.
 * @license     https://raw.githubusercontent.com/jbanety/blocketdtopmenu/master/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 *}
{extends file="helpers/form/form.tpl"}

{block name="label"}
    {if $input.type == 'customs'}

    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="legend"}
	<h3>
        {if isset($field.image)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
        {if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
        {$field.title}
        {if isset($field.count)}<span class="badge">{$field.count}</span>{/if}
		<span class="panel-heading-action">
			{foreach from=$toolbar_btn item=btn key=k}
                {if $k != 'modules-list' && $k != 'back'}
					<a id="desc-{$table}-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if}" class="list-toolbar-btn" {if isset($btn.href)}href="{$btn.href}"{/if} {if isset($btn.target) && $btn.target}target="_blank"{/if}{if isset($btn.js) && $btn.js}onclick="{$btn.js}"{/if}>
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s=$btn.desc}" data-html="true">
							<i class="process-icon-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if} {if isset($btn.class)}{$btn.class}{/if}" ></i>
						</span>
					</a>
                {/if}
            {/foreach}
			</span>
	</h3>
{/block}

{block name="input_row"}

    {if $input.type == 'hidden'}
		<input type="hidden" name="{$input.name}" id="{$input.name}" value={if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'html':'UTF-8'}{/if}" />
    {elseif $input.type == 'customs'}

		{assign var=customs value=$input.values}
    	{if $customs|count > 0}
			<div class="row">
				<table class="table">
					<thead>
					<tr>
						<th>{l s='Name' mod='blocketdcustom'}</th>
						<th>{l s='Hook' mod='blocketdcustom'}</th>
						<th class="text-center">{l s='Ordering' mod='blocketdcustom'}</th>
						<th class="text-center">{l s='Status' mod='blocketdcustom'}</th>
						<th>{l s='Access' mod='blocketdcustom'}</th>
						<th>{l s='Hook ETD' mod='blocketdcustom'}</th>
						<th>{l s='ID' mod='blocketdcustom'}</th>
						<th class="title_box text-right">{l s='Actions' mod='blocketdcustom'}</th>
					</tr>
					</thead>
					<tbody>
					{foreach $customs as $key => $custom}

						<tr {if $custom@iteration % 2}class="alt_row"{/if}>
							<td>
								<strong><a href="{$current}&token={$token}&editCustom&id_custom={(int)$custom.id}" title="{l s='Edit' mod='blocketdcustom'}">{$custom.title}</a></strong>
							</td>
							<td>
								{$custom.hook}
							</td>
							<td class="text-center">
								{if !$custom@first && isset($customs[$key-1]) && $customs[$key-1].hook == $custom.hook}
                                <a href="{$current}&token={$token}&orderUpCustom&id_custom={(int)$custom.id}" title="{l s='Order Up' mod='blocketdcustom'}" class="btn btn-link">
									<i class="icon-caret-square-o-up"></i>
								</a>
								{else}
									<span class="btn btn-link">&nbsp;&nbsp;&nbsp;&nbsp;</span>
								{/if}
								{if !$custom@last && isset($customs[$key+1]) && $customs[$key+1].hook == $custom.hook}
								 <a href="{$current}&token={$token}&orderDownCustom&id_custom={(int)$custom.id}" title="{l s='Order Down' mod='blocketdcustom'}" class="btn btn-link">
									<i class="icon-caret-square-o-down"></i>
								</a>
								{else}
									<span class="btn btn-link">&nbsp;&nbsp;&nbsp;&nbsp;</span>
								{/if}
							</td>
							<td class="text-center">
                                <i class="icon-{if $custom.published}check{else}times{/if}"></i>
							</td>
							<td>
								{if $custom.access == 0}
									{l s='Public'}
								{elseif $custom.access == 1}
									{l s='Guests'}
								{elseif $custom.access == 2}
									{l s='Customers'}
								{/if}
							</td>
							<td>
								{$custom.etdhook}
							</td>
							<td>
								{$custom.id}
							</td>
							<td>
								<div class="btn-group-action">
									<div class="btn-group pull-right">
										<a href="{$current}&token={$token}&editCustom&id_custom={(int)$custom.id}" title="{l s='Edit' mod='blocketdcustom'}" class="btn btn-default">
											<i class="icon-pencil"></i> {l s='Edit' mod='blocketdcustom'}
										</a>
										<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											<span class="caret"></span>&nbsp;
										</button>
										<ul class="dropdown-menu">
											<li>
												<a href="{$current}&token={$token}&deleteCustom&id_custom={(int)$custom.id}" title="{l s='Delete' mod='blocketdcustom'}"
												   onclick="return confirm('{l s='Do you really want to delete this custom block?' mod='blocketdcustom'}');">
													<i class="icon-trash"></i> {l s='Delete' mod='blocketdcustom'}
												</a>
											</li>
										</ul>
									</div>
								</div>
							</td>
						</tr>

					{/foreach}
					</tbody>
				</table>
			</div>
		{else}
			<div class="row alert alert-warning">{l s='No custom block found.' mod='blocketdcustom'}</div>
        {/if}

	{else}
		{$smarty.block.parent}
    {/if}

{/block}

{block name="input"}

	{if $input.type == 'exceptions'}
		<input type="text" name="{$input.name}" size="40" readonly value="{implode(',', $fields_value[$input.name])}" id="em_text">{if isset($input.required) && $input.required} <sup>*</sup>{/if}
		<div class="input-group" style="margin-top:5px">
			<select id="em_list">';
				{foreach $input.controllers as $k => $v}
					<option value="{$k}">{$k}</option>
				{/foreach}
			</select>
			<div class="input-group-btn">
				<button type="button" class="btn btn-default" id="btn-add-exception"><i class="icon-check"></i> {l s='Add'}</button>
				<button type="button" class="btn btn-default" id="btn-remove-exception"><i class="icon-remove"></i> {l s='Remove'}</button>
			</div>
		</div>

	{literal}
		<script>
			jQuery(document).ready(function($) {
			    var $list  = $('#em_list'),
			        $input = $('#em_text');
				$('#btn-add-exception').on('click', function() {
                    var v = $input.val();
                    if (v.length > 0) {
						v = v.split(",");
                        v.push($list.val());
                    } else {
                        v = [$list.val()];
                    }
                    $input.val(v.join(","));
                });
                $('#btn-remove-exception').on('click', function() {
                    var v = $input.val().split(",");
                    v = v.filter(function(element) {
                        return element !== $list.val();
                    });
                    $input.val(v.join(","));
                });
			});
		</script>
	{/literal}

    {elseif $input.type == 'hooks'}
		<div class="form-group">
			{block name="label"}{/block}
			<div class="col-lg-9">
				<select name="{$input.name}" id="{$input.name}">';
					<option value="">--</option>
					{foreach $input.hooks as $hook}
						<option value="{$hook}"{if $hook == $fields_value[$input.name]} selected{/if}>{$hook}</option>
					{/foreach}
				</select>
			</div>
		</div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
