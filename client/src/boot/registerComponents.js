import Injector from 'lib/Injector';
import PaletteColorField from 'components/PaletteColorField/PaletteColorField';

export default () => {
  Injector.component.registerMany({
    PaletteColorField,
  });
};
