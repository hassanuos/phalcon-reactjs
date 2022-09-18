import React from 'react';
import { Button, UncontrolledTooltip  } from 'reactstrap';
import api from './services/api';
import WidgetHead from '../src/components/widget-head';
import WidgetBody from '../src/components/widget-body';
import { useCountdown }  from '../src/hooks/useCountdown';
import './css/currency-flags.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import './css/App.css';

class App extends React.Component{
  constructor(props){
    super(props)
    this.state = {
      countries: [],
      baseCountry: "",
      baseCode: "",
      currentDay: "",
      loading: false,
      duration: 0,
    }
    this.refresh = this.refresh.bind(this);
  }

  refresh() {
    this.getExchangeData();
  }

  decrementCounter() {
    this.setState({ duration: this.state.duration - 1 });

    console.log(this.state.duration);

    if (this.state.duration === 0){
      this.getExchangeData();
    }
  }

  componentDidMount(){
    this.getExchangeData();
    const thisBoundedDecrement = this.decrementCounter.bind(this);
    setInterval(thisBoundedDecrement, 1000);
  }

  getExchangeData(){
    this.setState({loading: true});

    api.get(`/get-exchange-data`)
        .then(res =>{
          let apiData = res.data.data;
          this.setState({
            countries: apiData.rates,
            baseCountry: apiData.base_country_name,
            baseCode: apiData.base,
            currentDay: apiData.date,
            loading: false,
            duration: apiData.cache_expiry_time
          });
        })
        .catch(err => console.log(err))
  }
  
  render(){
    return(
      <div>
        <div className="panel">
          <div className="div-center">
            <div className="content">
              <UncontrolledTooltip placement="right" target="UncontrolledTooltipExample">
                Refresh Data
              </UncontrolledTooltip>
              <Button id="UncontrolledTooltipExample" close onClick={this.refresh}>
                <i className="fa fa-history btn-icon" aria-hidden="true"></i>
              </Button>
              <WidgetHead baseCode={this.state.baseCode} isLoading={this.state.loading}></WidgetHead>
              {this.state.loading ? <div>Loading...</div> : <WidgetBody baseCode={this.state.baseCode} countries={this.state.countries} currentDay={this.state.currentDay} expiryTime={this.state.duration}></WidgetBody>}
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default App;