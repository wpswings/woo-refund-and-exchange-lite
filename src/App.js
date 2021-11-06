import {useState} from 'react';
import { makeStyles } from '@material-ui/core/styles';
import { Button, Typography, Container, CircularProgress} from '@material-ui/core';
import Stepper from './component/Stepper';
import FirstStep from './component/FirstStep';
import FinalStep from './component/FinalStep';
import Context from './store/store';
import axios from 'axios';
import { __ } from '@wordpress/i18n';
import qs from 'qs';
const useStyles = makeStyles((theme) => ({
    instructions: {
        marginTop: theme.spacing(1),
        marginBottom: theme.spacing(1),
    },
}));
function App(props) {
    const [loading, setLoading] = useState(false);
    const [state, setState] = useState({
        checkedRefund: false,
        checkedOrderMsg: false,
        checkedOrderMsgEmail:false,
        checkedExchange: false,
        checkedCancel: false,
        checkedCancelProd:false,
        checkedWallet: false,
        checkedCOD : false,
        consetCheck: true,
        checkedResetLicense:false,
        licenseCode:'',
    });
    const classes = useStyles();
    const [activeStep, setActiveStep] = useState(0);
    const steps = [ __( 'General Settings', 'woo-refund-and-exchange-lite' ), __( 'Final Step', 'woo-refund-and-exchange-lite' )];

    
    const onFormFieldHandler = (event) => {
        let value = ('checkbox' === event.target.type ) ? event.target.checked : event.target.value;
        setState({ ...state, [event.target.name]: value });
    };
    const getStepContent = (stepIndex) => {
        switch (stepIndex) {
            case 0:
                return (<FirstStep />);
            case 1:
                return (<FinalStep/>);
            case 2:
                return <h1>{__( 'Thanks for your details', 'woo-refund-and-exchange-lite' )}</h1>;
            default:
                return __( 'Unknown stepIndex', 'woo-refund-and-exchange-lite' );
        }
    }
    const handleNext = () => {
        setActiveStep((prevActiveStep) => prevActiveStep + 1);
    };

    const handleBack = () => {
        setActiveStep((prevActiveStep) => prevActiveStep - 1);
    };

    const handleFormSubmit = (e) => {
        e.preventDefault();
        setLoading(true);
        const user = {
            ...state,
            'action': 'mwb_standard_save_settings_filter',
            nonce: frontend_ajax_object.mwb_standard_nonce,
        };
        
        axios.post(frontend_ajax_object.ajaxurl, qs.stringify(user) )
            .then(res => {
                setLoading(false);
                console.log( res.data);
                handleNext();
                setTimeout(() => {
                    window.location.href = frontend_ajax_object.redirect_url; 
                    return null;
                }, 3000);
            }).catch(error=>{
                console.log(error);
        })
        
    }

    let nextButton = (
        <Button
            variant="contained" color="primary" onClick={handleNext} size="large">
            Next
        </Button>
    );
    if (activeStep === steps.length-1 ) {
        nextButton = (
            <Button
                onClick={handleFormSubmit}
                variant="contained" color="primary" size="large">
                Finish
            </Button>
        )
    } 
    return (
        <Context.Provider value={{
            formFields:state,
            changeHandler:  onFormFieldHandler,  
        }}>
            <div className="mwbMsfWrapper">
                <Stepper activeStep={activeStep} steps={steps}/>
                <div className="mwbHeadingWrap">
                    <h2>{__( 'Welcome to Makewebbetter', 'woo-refund-and-exchange-lite' ) }</h2>
                    <p>{__('Complete The steps to get started','woo-refund-and-exchange-lite') }</p>
                </div>
                <Container maxWidth="sm">
                    <form className="mwbMsf">
                        <Typography className={classes.instructions}>
                            {(loading) ? <CircularProgress className="mwbCircularProgress" /> :getStepContent(activeStep)}
                        </Typography>
                        <div className="mwbButtonWrap">
                            {activeStep !== steps.length && <Button
                                disabled={activeStep === 0}
                                onClick={handleBack}
                                variant="contained" size="large">
                            Back
                            </Button>}
                            {activeStep !== steps.length && nextButton}
                        </div>
                    </form>
                </Container >
            </div>
        </Context.Provider>
    );
}

export default App;