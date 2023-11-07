import HamburguerMenu from './HamburguerMenu';

describe('HamburguerMenu class', () => {
    let button;
    let menuElement;

    beforeEach(() => {
        button = document.createElement('button');
        menuElement = document.createElement('menuElement');
    });

    it('add active class to menuElement on button click', () => {
        const hamburguerMenu = new HamburguerMenu(button, menuElement);

        hamburguerMenu.toggleActiveOnClick();
        button.click();
    
        expect([ ...menuElement.classList ]).toContain("active");
    });

    it('remove active class in menuElement on button click', () => {
        const hamburguerMenu = new HamburguerMenu(button, menuElement);

        menuElement.classList.add('active');
        hamburguerMenu.toggleActiveOnClick();
        button.click();
    
        expect([ ...menuElement.classList ]).not.toContain("active");
    });
})