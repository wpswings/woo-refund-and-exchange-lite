import React,{useContext} from 'react';
import { Select, FormGroup, InputLabel, MenuItem, Checkbox, FormControlLabel, FormControl } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { __ } from '@wordpress/i18n';
import Context from '../store/store';
const useStyles = makeStyles({
      margin: {
        marginBottom: '20px',
      },
});
const SecondStep = (props) => {
    const classes = useStyles();
    const ctx = useContext(Context);
    return ( 
    <>
        <h3 className="mwb-title">{__('Product Setting','woo-refund-and-exchange-lite') }</h3>
        <FormControl component="fieldset" variant="outlined" fullWidth className="fieldsetWrapper">
            <InputLabel id="demo-simple-select-outlined-label">{__('Age','woo-refund-and-exchange-lite') }</InputLabel>
            <Select
                labelId="demo-simple-select-outlined-label"
                name="age"
                id="demo-simple-select-outlined"
                value={ctx.formFields['age']}
                onChange={ctx.changeHandler}
                label="Age"
                className={classes.margin}>
                <MenuItem value="">{__('None', 'woo-refund-and-exchange-lite') }</MenuItem>
                <MenuItem value={10}>{ __('Ten', 'woo-refund-and-exchange-lite') }</MenuItem>
                <MenuItem value={20}>{ __( 'Twenty', 'woo-refund-and-exchange-lite' ) }</MenuItem>
                <MenuItem value={30}>{ __( 'Thirty','woo-refund-and-exchange-lite' ) }</MenuItem>
            </Select>
        </FormControl>
        <FormGroup>
            <FormControlLabel
                control={
                <Checkbox
                    checked={ctx.formFields['FirstCheckbox']}
                    onChange={ctx.changeHandler}
                    name="FirstCheckbox"
                    color="primary"
                />
                }
                label="Primary"
                className="mwbFormLabel" />
        </FormGroup>
    </>
    )
}
export default SecondStep;