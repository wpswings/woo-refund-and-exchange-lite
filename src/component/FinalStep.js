import React,{useContext,Fragment} from 'react';
import Context from '../store/store';
import {Radio,RadioGroup, FormControlLabel, Switch, FormControl, FormLabel, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { __ } from '@wordpress/i18n';
const useStyles = makeStyles({
      margin: {
        marginBottom: '20px',
      },
});
export default function FinalStep(props) {
    const classes = useStyles();
    const ctx = useContext(Context)
    return (
        <Fragment>
            <FormControl component="fieldset" fullWidth className="fieldsetWrapper">
            <FormLabel component="legend" className="mwbFormLabel">{ __('Bingo! You are all set to take advantage of your business. Lastly, we urge you to allow us collect some','woo-refund-and-exchange-lite')} <a href='https://makewebbetter.com/plugin-usage-tracking/' target="_blank" >{__('information','woo-refund-and-exchange-lite') }</a> { __( 'in order to improve this plugin and provide better support. If you want, you can dis-allow anytime settings, We never track down your personal data. Promise!', 'woo-refund-and-exchange-lite') }</FormLabel>
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['consetCheck']}
                onChange={ctx.changeHandler}
                name="consetCheck"
                color="primary"
            />
            } className={classes.margin} />
            </FormControl>
            <FormControlLabel
            control={
            <Switch
                checked={ctx.formFields['checkedResetLicense']}
                onChange={ctx.changeHandler}
                name="checkedResetLicense"
                color="primary"
               />
            }
            label={ __( 'Enable Reset License on the plugin deactivation', 'woo-refund-and-exchange-lite' ) }
            className={classes.margin} />
            <FormControl component="fieldset" fullWidth className="fieldsetWrapper">
            <TextField 
                value={ctx.formFields['licenseCode']}
                onChange={ctx.changeHandler} 
                id="licenseCode" 
                name="licenseCode" 
                label={__('Enter your license code', 'woo-refund-and-exchange-lite' )}  variant="outlined" className={classes.margin}/>
            </FormControl>
        </Fragment> 
    );
}