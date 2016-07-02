<?php $view->script('settings', 'sitemap:app/bundle/settings.js', ['vue', 'jquery']) ?>

<div id="settings" class="uk-form uk-form-horizontal" v-cloak>

    <div class="uk-form-row">
        <label class="uk-form-label">{{ 'Default frequency' | trans }}</label>
        <div class="uk-form-controls">
            <input type="text" v-model="config.frequency">
            <button class="uk-button uk-button-info" @click.prevent="save">{{ 'Save' | trans }}</button>
        </div>
        
        
    </div>

    <div class="uk-form-row">
        <button class="uk-button uk-button-primary uk-button-large" @click.prevent="generate">{{ 'Generate' | trans }}</button>
    </div>
</div>
