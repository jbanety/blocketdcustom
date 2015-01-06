{**
 * @package     blocketdcustom
 *
 * @version     0.0.1
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
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

{block name="input"}

	{if $input.type == 'exceptions'}
		<input class="form-control" type="text" name="{$input.name}" size="40" value="{implode(',', $fields_value[$input.name])}" id="{$input.name}">
	{elseif $input.type == 'hooks'}
		{assign var=customfield value=$input.name|cat:'_custom'}
		<select class="form-control fixed-width-xl" name="{$input.name}" id="{$input.name}">
			<option value="">----------</option>
			<option value="custom">Personnalisé</option>
			<option value="">----------</option>
		{foreach $input.hooks as $hook}
			<option value="{$hook}"{if $hook == $fields_value[$input.name]} selected{/if}>{$hook}</option>
		{/foreach}
		</select>
		<input class="form-control" type="text" name="{$customfield}" value="{$fields_value[$customfield]}" id="{$customfield}">
		<script>
			jQuery(document).ready(function() {ldelim}
				$('#{$input.name}').on('change', function() {ldelim}
					var $this = $(this),
						$custom = $('#{$customfield}');
					if ($this.val() == "custom") {ldelim}
						$custom.show();
					{rdelim} else {ldelim}
						$custom.hide();
					{rdelim}
				{rdelim});
			{rdelim});
		</script>
	{else}
		{$smarty.block.parent}
    {/if}

{/block}

{block name="input_row"}

	{if $input.type == 'customs'}

		{assign var=customs value=$input.values}
		{if isset($customs) && count($customs) > 0}
			<div class="row">
				<div class="col-lg-9 col-lg-offset-3">
					<div class="panel">
						<div class="panel-heading">
							{$input.label}
						</div>
						<table class="table">
							<thead>
							<tr>
								<th>
									<input type="checkbox" name="checkme" id="checkme" class="noborder" onclick="checkDelBoxes(this.form, '{$input.name}', this.checked)" />
								</th>
								<th>{l s='Name' mod='blocketdcustom'}</th>
								<th>{l s='Status' mod='blocketdcustom'}</th>
								<th>{l s='Ordering' mod='blocketdcustom'}</th>
								<th>{l s='Access' mod='blocketdcustom'}</th>
								<th>{l s='Hook' mod='blocketdcustom'}</th>
								<th>{l s='ID' mod='blocketdcustom'}</th>
								<th>{l s='Actions' mod='blocketdcustom'}</th>
							</tr>
							</thead>
							<tbody>
							{foreach $customs as $key => $custom}
								<tr {if $key%2}class="alt_row"{/if}>
									<td>
										{assign var=id_checkbox value=$custom.id}
										<input type="checkbox" class="cmsBox" name="{$input.name}" id="{$id_checkbox}" value="{$id_checkbox}" {if isset($fields_value[$id_checkbox])}checked="checked"{/if} />
									</td>
									<td>
										<label class="control-label" for="{$id_checkbox}">{$custom.title|escape}</label>
									</td>
									<td>
										<img src="{$smarty.const._PS_ADMIN_IMG_}{if $custom.published}enabled.gif{else}disabled.gif{/if}" alt="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" title="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" />
									</td>
									<td>
										{if isset($custom.parent_id) && isset($input.ordering[$custom.parent_id][$orderkey - 1])}
											<a href="{$current}&token={$token}&orderUp&id_custom={(int)$custom.id}">
												<img src="{$smarty.const._PS_ADMIN_IMG_}{if $order_way == 'ASC'}down{else}up{/if}.gif" alt="{l s='Down'}" title="{l s='Down'}" />
											</a>
										{/if}
										{if isset($custom.parent_id) && isset($input.ordering[$custom.parent_id][$orderkey + 1])}
											<a href="{$current}&token={$token}&orderDown&id_custom={(int)$custom.id}">
												<img src="{$smarty.const._PS_ADMIN_IMG_}{if $order_way == 'ASC'}up{else}down{/if}.gif" alt="{l s='Up'}" title="{l s='Up'}" />
											</a>
										{/if}
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
										{$custom.hook}
									</td>
									<td>
										{$custom.id}
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		{else}
			<p>{l s='No custom blocks have been created.' mod='blocketdcustom'}</p>
		{/if}

	{else}
		{$smarty.block.parent}
	{/if}

{/block}
